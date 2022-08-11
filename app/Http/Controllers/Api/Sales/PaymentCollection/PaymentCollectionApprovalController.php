<?php

namespace App\Http\Controllers\Api\Sales\PaymentCollection;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Http\Resources\ApiCollection;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use Illuminate\Http\Request;
use App\Model\UserActivity;
use App\Model\Token;
use App\Model\Master\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentCollectionApprovalRequestSent;
use App\Mail\PaymentCollectionApprovalRequestSentSingle;
use App\Mail\PaymentCollectionCancellationApprovalRequestSentSingle;

class PaymentCollectionApprovalController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $filter_like = $request->get('filter_like');
        $paymentCollections = PaymentCollection::join('forms', 'forms.formable_id', '=', PaymentCollection::getTableName().'.id')
            ->where(['forms.formable_type' => PaymentCollection::$morphName, 'forms.approval_status' => 0])
            ->whereNotNull('number')
            ->whereRaw("(number like '%" . $filter_like . "%' 
                or customers.name like '%" . $filter_like . "%'
                or date like '%" . $filter_like . "%')")
            ->join('customers', 'customers.id', '=', PaymentCollection::getTableName().'.customer_id')
            ->select('sales_payment_collections.id', 'date', 'number', 'customers.name as customer', 'amount', 'last_request_date', 'approval_status')
            ->orderBy('date', 'desc');

        $user_request = UserActivity::where(['activity' => 'Request Approval', 'table_type' => 'forms'])
            ->select('table_id')
            ->selectRaw('MAX(date) as last_request_date')
            ->groupBy('table_id');
     
        $paymentCollections = $paymentCollections->leftJoinSub($user_request, 'user_request', function ($q) {
            $q->on('forms.id', '=', 'user_request.table_id');
        });
        
        $paymentCollections = pagination($paymentCollections, $request->get('limit'));

        return new ApiCollection($paymentCollections);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $paymentCollection = PaymentCollection::findOrFail($id);

        $isSuccess = PaymentCollection::checkAvailableReference($paymentCollection);

        $paymentCollection->form->approval_by = auth()->user()->id;
        $paymentCollection->form->approval_at = now();
        if ($isSuccess) {
            $paymentCollection->form->approval_status = 1;
            foreach ($paymentCollection->details as $detail) {
                if ($detail->referenceable_type === SalesInvoice::$morphName) {
                    if ($detail->available === $detail->amount) {
                        $salesInvoice = SalesInvoice::find($detail->referenceable_id);
                        $salesInvoice->form->done = 1;
                        $salesInvoice->form->save();
                    } else {
                        $salesInvoice = SalesInvoice::find($detail->referenceable_id);
                        $salesInvoice->form->done = 0;
                        $salesInvoice->form->save();
                    }
                }
    
                if ($detail->referenceable_type === SalesReturn::$morphName) {
                    if ($detail->available === $detail->amount) {
                        $salesReturn = SalesReturn::find($detail->referenceable_id);
                        $salesReturn->form->done = 1;
                        $salesReturn->form->save();
                    } else {
                        $salesReturn = SalesReturn::find($detail->referenceable_id);
                        $salesReturn->form->done = 0;
                        $salesReturn->form->save();
                    }
                }
            }
        } else {
            $paymentCollection->form->approval_status = 0;
        }        
        $paymentCollection->form->save();
        
        return new ApiResource($paymentCollection);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        if ($request->get('reason') === null) {
            throw new PointException();
        }
        $paymentCollection = PaymentCollection::findOrFail($id);
        $paymentCollection->form->approval_by = auth()->user()->id;
        $paymentCollection->form->approval_at = now();
        $paymentCollection->form->approval_reason = $request->get('reason');
        $paymentCollection->form->approval_status = -1;
        $paymentCollection->form->save();

        return new ApiResource($paymentCollection);
    }

    /**
     * Send approval request to a specific approver.
     */
    public function sendApprovalSingle(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();
        $paymentCollection = PaymentCollection::findOrFail($id);

        $createdBy = User::findOrFail($paymentCollection->form->created_by);
        $approver = User::findOrFail($paymentCollection->form->request_approval_to);

        $userActivity = UserActivity::where('number', $paymentCollection->form->number);
        $userActivity = $userActivity->where('activity', 'like', '%' . 'Update' . '%');
        $updateCount = $userActivity->count();
        if ($updateCount > 0) {
            $form['action'] = 'update';
        } else {
            $form['action'] = 'create';
        }

        $form['createdBy'] = $createdBy->full_name;
        $form['total_invoice'] = $request['total_invoice'];
        $form['total_down_payment'] = $request['total_down_payment'];
        $form['total_return'] = $request['total_return'];
        $form['total_other'] = $request['total_other'];
        
        // create token based on request_approval_to
        $token = Token::where('user_id', $approver->id)->first();
        
        if (!$token) {
            $token = new Token([
                'user_id' => $approver->id,
                'token' => md5($approver->email.''.now()),
            ]);
            $token->save();
        }



        Mail::to([
            $approver->email,
        ])->queue(new PaymentCollectionApprovalRequestSentSingle(
            $paymentCollection,
            $approver,
            $form,
            $_SERVER['HTTP_REFERER'],
            $paymentCollection->id,
            $token->token
        ));

        return [
            'input' => $request->all(),
        ];
    }

    /**
     * Send approval request to a specific approver.
     */
    public function sendCancellationApprovalSingle(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();
        $paymentCollection = PaymentCollection::findOrFail($id);

        $cancelBy = User::findOrFail($paymentCollection->form->request_cancellation_by);
        $approver = User::findOrFail($paymentCollection->form->request_cancellation_to);

        $form['action'] = 'delete';
        $form['cancelBy'] = $cancelBy->full_name;
        $form['total_invoice'] = $request['total_invoice'];
        $form['total_down_payment'] = $request['total_down_payment'];
        $form['total_return'] = $request['total_return'];
        $form['total_other'] = $request['total_other'];
        
        // create token based on request_approval_to
        $token = Token::where('user_id', $approver->id)->first();

        if (!$token) {
            $token = new Token([
                'user_id' => $approver->id,
                'token' => md5($approver->email.''.now()),
            ]);
            $token->save();
        }



        Mail::to([
            $approver->email,
        ])->queue(new PaymentCollectionCancellationApprovalRequestSentSingle(
            $paymentCollection,
            $approver,
            $form,
            $_SERVER['HTTP_REFERER'],
            $paymentCollection->id,
            $token->token
        ));

        return [
            'input' => $request->all(),
        ];
    }

    /**
     * Send approval request to a specific approver.
     */
    public function sendApproval(Request $request)
    {
        DB::connection('tenant')->beginTransaction();
        $paymentCollections = PaymentCollection::whereIn('id', $request->ids)->get();

        $detailsReferences = array();
        foreach ($paymentCollections as $paymentCollection) {
            $isDetailValid = true;
            if ($paymentCollection->form->cancellation_status === null) {
                foreach ($paymentCollection->details as $detail) {
                    if ($detail->referenceable_form_number) {
                        if(array_key_exists($detail->referenceable_form_number, $detailsReferences)) {
                            if ($detailsReferences[$detail->referenceable_form_number] <= $detail->amount) {
                                $isDetailValid = false;
                                break;
                            } else {
                                $detailsReferences[$detail->referenceable_form_number] -= $detail->amount;
                            }
                        } else {
                            $detailsReferences[$detail->referenceable_form_number] = $detail->amount;
                        }
                    }                
                }                
            }
            
            if (!$isDetailValid) {
                continue;
            }

            $datas[$paymentCollection->form->request_approval_to][] = $paymentCollection;
        }

        foreach ($datas as $data) {
            $no = 1;
            $ids = '';
            foreach ($data as $i => $paymentCollection) {
                $paymentCollection['no'] = $no;
                $paymentCollection['created_by'] = User::findOrFail($paymentCollection->form->created_by)->getFullNameAttribute();
                if ($paymentCollection->form->cancellation_status === 0) {
                    $paymentCollection['action'] = 'delete';
                } else if ($paymentCollection->form->cancellation_status === null and $paymentCollection->form->approval_status === 0) {
                    $userActivity = UserActivity::where('number', $paymentCollection->form->number);
                    $userActivity = $userActivity->where('activity', 'like', '%' . 'Update' . '%');
                    $updateCount = $userActivity->count();
                    if ($updateCount > 0) {
                        $paymentCollection['action'] = 'update';
                    } else {
                        $paymentCollection['action'] = 'create';
                    }
                }
                
                $no++;
                $ids .= $paymentCollection->id . ',';
            }
            $ids = substr($ids, 0, -1);
            $approver = User::findOrFail($data[0]->form->request_approval_to);

            // create token based on request_approval_to
            $token = Token::where('user_id', $approver->id)->first();

            if (!$token) {
                $token = new Token([
                    'user_id' => $approver->id,
                    'token' => md5($approver->email.''.now()),
                ]);
                $token->save();
            }

            if (count($data) > 1) {
                $form['number'] = $data[0]->form->number . ' - ' . end($data)->form->number;
                $form['date'] = date('d F Y', strtotime($data[0]->form->date)) . ' - ' . date('d F Y', strtotime(end($data)->form->date));
                $form['created'] = date('d F Y H:i:s', strtotime($data[0]->form->created_at)) . ' - ' . date('d F Y H:i:s', strtotime(end($data)->form->created_at));
            } else {
                $form['number'] = $data[0]->form->number;
                $form['date'] = $data[0]->form->date;
                $form['created'] = $data[0]->form->created_at;
            };

            DB::connection('tenant')->commit();

            Mail::to([
                $approver->email,
            ])->queue(new PaymentCollectionApprovalRequestSent(
                $data,
                $approver,
                $form,
                $_SERVER['HTTP_REFERER'],
                $ids,
                $token->token
            ));
        }

        return [
            'input' => $request->all(),
        ];
    }
}
