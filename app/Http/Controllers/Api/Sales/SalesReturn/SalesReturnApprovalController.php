<?php

namespace App\Http\Controllers\Api\Sales\SalesReturn;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;

use App\Model\Token;
use App\Model\Form;
use App\Model\UserActivity;
use App\Model\Sales\SalesReturn\SalesReturn;

use App\Mail\Sales\SalesReturnApprovalRequest;

class SalesReturnApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $salesReturns = SalesReturn::from(SalesReturn::getTableName().' as '.SalesReturn::$alias)->eloquentFilter($request);

        $salesReturns = SalesReturn::joins($salesReturns, $request->get('join'))
            ->whereNull(Form::$alias . '.edited_number')
            ->where(Form::$alias . '.close_status', 0)
            ->orWhere(function ($query) {
                $query
                    ->where(Form::$alias . '.cancellation_status', 0)
                    ->whereNull(Form::$alias . '.close_status')
                    ->whereNull(Form::$alias . '.edited_number');
            })
            ->orWhere(function ($query) {
                $query
                    ->where(Form::$alias . '.approval_status', 0)
                    ->whereNull(Form::$alias . '.cancellation_status')
                    ->whereNull(Form::$alias . '.close_status')
                    ->whereNull(Form::$alias . '.edited_number');
            });

        $userActivity = UserActivity::where(['activity' => 'Request Approval', 'table_type' => SalesReturn::$morphName])
            ->select('table_id')
            ->selectRaw('MAX(date) as last_request_date')
            ->groupBy('table_id');
     
        $salesReturns = $salesReturns
            ->leftJoinSub($userActivity, 'user_activity', function ($q) {
                $q->on('form.formable_id', '=', 'user_activity.table_id');
            })
            ->addSelect('user_activity.last_request_date');
        
        $salesReturns = pagination($salesReturns, $request->get('limit'));

        return new ApiCollection($salesReturns);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        
        $result = DB::connection('tenant')->transaction(function () use ($id) {
        $salesReturn = SalesReturn::findOrFail($id);
        $salesReturn->checkQuantity($salesReturn->items);

        $form = $salesReturn->form;
        $form->approval_by = auth()->user()->id;
        $form->approval_at = now();
        $form->approval_status = 1;
        $form->save();

        SalesReturn::updateJournal($salesReturn);
        SalesReturn::updateInventory($salesReturn->form, $salesReturn);
        SalesReturn::updateInvoiceQuantity($salesReturn, 'update');
        

        $form->fireEventApproved();
    
            return new ApiResource($salesReturn);
        });
        

        return $result;
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([ 'reason' => 'required' ]);

        $result = DB::connection('tenant')->transaction(function () use ($request, $validated, $id) {
            $salesReturn = SalesReturn::findOrFail($id);
            $salesReturn->form->approval_by = auth()->user()->id;
            $salesReturn->form->approval_at = now();
            $salesReturn->form->approval_reason = $validated['reason'];
            $salesReturn->form->approval_status = -1;
            $salesReturn->form->save();
    
            $salesReturn->form->fireEventRejected();
    
            return new ApiResource($salesReturn);
        });

        return $result;
    }

    
    /**
     * Send approval request to a specific approver.
     */
    public function sendApproval(Request $request)
    {
        $salesReturnByApprovers = [];

        DB::connection('tenant')->transaction(function () use ($request, $salesReturnByApprovers) {
            $sendBy = tenant(auth()->user()->id);
            $salesReturns = SalesReturn::whereIn('id', $request->ids)->get();

            // delivery order grouped by approver
            foreach ($salesReturns as $do) { 
                $salesReturnByApprovers[$do->form->request_approval_to][] = $do;
            }
    
            foreach ($salesReturnByApprovers as $salesReturnByApprover) {
                $approver = null;

                $formStart = head($salesReturnByApprover)->form;
                $formEnd = last($salesReturnByApprover)->form;

                $form = [
                    'number' => $formStart->number,
                    'date' => $formStart->date,
                    'created' => $formStart->created_at,
                    'send_by' => $sendBy
                ];

                // loop each sales return by group approver
                foreach ($salesReturnByApprover as $salesReturn) {
                    $salesReturn->action = 'create';
                    
                    if(!$approver) {
                        $approver = $salesReturn->form->requestApprovalTo;
                        // create token based on request_approval_to
                        $approverToken = Token::where('user_id', $approver->id)->first();
                        if (!$approverToken) {
                            $approverToken = new Token();
                            $approverToken->user_id = $approver->id;
                            $approverToken->token = md5($approver->email.''.now());
                            $approverToken->save();
                        }

                        $approver->token = $approverToken->token;
                    }
                    
                    if ($salesReturn->form->close_status === 0) $salesReturn->action = 'close';

                    if (
                        $salesReturn->form->cancellation_status === 0
                        && $salesReturn->form->close_status === null
                    ) {
                        $salesReturn->action = 'delete';
                    }

                    if (
                        $salesReturn->form->cancellation_status === null 
                        && $salesReturn->form->close_status === null
                        && $salesReturn->form->approval_status === 0
                    ) {
                        $userActivity = UserActivity::where('number', $salesReturn->form->number)
                            ->where('activity', 'like', '%' . 'Update' . '%');

                        $updateCount = $userActivity->count();
                        if ($updateCount > 0) $salesReturn->action = 'update';
                    }

                    $salesReturn->form->fireEventRequestApproval();
                }
    
                if (count($salesReturnByApprover) > 1) {
                    $formattedFormStartDate = date('d F Y', strtotime($formStart->date));
                    $formattedFormEndDate = date('d F Y', strtotime($formEnd->date));
                    $formattedFormStartCreate = date('d F Y H:i:s', strtotime($formStart->created_at));
                    $formattedFormEndCreate = date('d F Y H:i:s', strtotime($formEnd->created_at));

                    $form['number'] = $formStart->number . ' - ' . $formEnd->number;
                    $form['date'] = $formattedFormStartDate . ' - ' . $formattedFormEndDate;
                    $form['created'] = $formattedFormStartCreate . ' - ' . $formattedFormEndCreate;
                }

                $approvalRequest = new SalesReturnApprovalRequest($salesReturnByApprover, $approver, (object) $form, $_SERVER['HTTP_REFERER']);
                Mail::to([ $approver->email ])->queue($approvalRequest);
            }
        });

        // return $result;
        return [
            'input' => $request->all(),
        ];
    }
}
