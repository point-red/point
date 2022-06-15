<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;

use App\Model\Token;
use App\Model\Form;
use App\Model\UserActivity;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;

use App\Mail\Sales\DeliveryOrderApprovalRequestSent;

class DeliveryOrderApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $deliveryOrders = DeliveryOrder::from(DeliveryOrder::getTableName().' as '.DeliveryOrder::$alias)->eloquentFilter($request);

        $deliveryOrders = DeliveryOrder::joins($deliveryOrders, $request->get('join'))
            ->where(Form::$alias . '.close_status', 0)
            ->orWhere(function ($query) {
                $query
                    ->where(Form::$alias . '.cancellation_status', 0)
                    ->whereNull(Form::$alias . '.close_status');
            })
            ->orWhere(function ($query) {
                $query
                    ->where(Form::$alias . '.approval_status', 0)
                    ->whereNull(Form::$alias . '.cancellation_status')
                    ->whereNull(Form::$alias . '.close_status');
            });

        $userActivity = UserActivity::where(['activity' => 'Request Approval', 'table_type' => 'forms'])
            ->select('table_id')
            ->selectRaw('MAX(date) as last_request_date')
            ->groupBy('table_id');
     
        $deliveryOrders = $deliveryOrders->leftJoinSub($userActivity, 'user_activity', function ($q) {
            $q->on('form.id', '=', 'user_activity.table_id');
        })
        ->addSelect('user_activity.last_request_date');
        
        $deliveryOrders = pagination($deliveryOrders, $request->get('limit'));

        return new ApiCollection($deliveryOrders);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        
        $result = DB::connection('tenant')->transaction(function () use ($id) {
            $deliveryOrder = DeliveryOrder::findOrFail($id);

            $form = $deliveryOrder->form;
            $form->approval_by = auth()->user()->id;
            $form->approval_at = now();
            $form->approval_status = 1;
            $form->save();
    
            $salesOrder = $deliveryOrder->salesOrder;
            if ($salesOrder) {
                $salesOrder->updateStatus();
            }

            $form->fireEventApproved();
    
            return new ApiResource($deliveryOrder);
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
            $deliveryOrder = DeliveryOrder::findOrFail($id);
            $deliveryOrder->form->approval_by = auth()->user()->id;
            $deliveryOrder->form->approval_at = now();
            $deliveryOrder->form->approval_reason = $validated['reason'];
            $deliveryOrder->form->approval_status = -1;
            $deliveryOrder->form->save();
    
            $deliveryOrder->form->fireEventRejected();
    
            return new ApiResource($deliveryOrder);
        });

        return $result;
    }

    
    /**
     * Send approval request to a specific approver.
     */
    public function sendApproval(Request $request)
    {
        $deliveryOrderByApprovers = [];

        DB::connection('tenant')->transaction(function () use ($request, $deliveryOrderByApprovers) {
            $deliveryOrders = DeliveryOrder::whereIn('id', $request->ids)->get();

            // delivery order grouped by approver
            foreach ($deliveryOrders as $do) { 
                $deliveryOrderByApprovers[$do->form->request_approval_to][] = $do;
            }
    
            foreach ($deliveryOrderByApprovers as $deliveryOrdersByApprover) {
                $approver = null;

                $formStart = head($deliveryOrdersByApprover)->form;
                $formEnd = last($deliveryOrdersByApprover)->form;

                $form = [
                    'number' => $formStart->number,
                    'date' => $formStart->date,
                    'created' => $formStart->created_at,
                ];

                // loop each delivery order by group approver
                foreach ($deliveryOrdersByApprover as $deliveryOrder) {
                    $deliveryOrder->action = 'create';
                    
                    if(!$approver) {
                        $approver = $deliveryOrder->form->requestApprovalTo;
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
                    
                    if ($deliveryOrder->form->close_status === 0) $deliveryOrder->action = 'close';

                    if (
                        $deliveryOrder->form->cancellation_status === 0
                        && $deliveryOrder->form->close_status === null
                    ) {
                        $deliveryOrder->action = 'delete';
                    }

                    if (
                        $deliveryOrder->form->cancellation_status === null 
                        && $deliveryOrder->form->close_status === null
                        && $deliveryOrder->form->approval_status === 0
                    ) {
                        $userActivity = UserActivity::where('number', $deliveryOrder->form->number)
                            ->where('activity', 'like', '%' . 'Update' . '%');

                        $updateCount = $userActivity->count();
                        if ($updateCount > 0) $deliveryOrder->action = 'update';
                    }

                    $deliveryOrder->form->fireEventRequestApproval();
                }
    
                if (count($deliveryOrdersByApprover) > 1) {
                    $formattedFormStartDate = date('d F Y', strtotime($formStart->date));
                    $formattedFormEndDate = date('d F Y', strtotime($formEnd->date));
                    $formattedFormStartCreate = date('d F Y H:i:s', strtotime($formStart->created_at));
                    $formattedFormEndCreate = date('d F Y H:i:s', strtotime($formEnd->created_at));

                    $form['number'] = $formStart->number . ' - ' . $formEnd->number;
                    $form['date'] = $formattedFormStartDate . ' - ' . $formattedFormEndDate;
                    $form['created'] = $formattedFormStartCreate . ' - ' . $formattedFormEndCreate;
                }

                $approvalRequest = new DeliveryOrderApprovalRequestSent($deliveryOrdersByApprover, $approver, (object) $form);
                Mail::to([ $approver->email ])->queue($approvalRequest);
            }
        });

        // return $result;
        return [
            'input' => $request->all(),
        ];
    }
}
