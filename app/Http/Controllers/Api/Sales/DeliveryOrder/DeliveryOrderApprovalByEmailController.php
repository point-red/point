<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;

use App\Model\UserActivity;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;

class DeliveryOrderApprovalByEmailController extends Controller
{
    protected $request;

    protected function _storeUserActivity($activity, $deliveryOrder) {
        $userActivity = new UserActivity;
        $userActivity->table_type = $deliveryOrder::$morphName;
        $userActivity->table_id = $deliveryOrder->id;
        $userActivity->number = $deliveryOrder->form->number;
        $userActivity->date = now();
        $userActivity->user_id = $this->request->approver_id;
        $userActivity->activity = $activity;
        $userActivity->save();        
    }
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request)
    {
        $this->request = $request;

        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $deliveryOrders = DeliveryOrder::whereIn('id', $request->ids)->get();
            
            foreach ($deliveryOrders as $deliveryOrder) {  
                $form = $deliveryOrder->form;

                // approve cancellation form
                if ($form->cancellation_status === 0 && is_null($form->close_status)) {
                    $form->cancellation_approval_by = $request->approver_id;
                    $form->cancellation_approval_at = now();
                    $form->cancellation_status = 1;
                    $form->save();

                    if ($deliveryOrder->salesOrder) {
                        $deliveryOrder->salesOrder->form->done = false;
                        $deliveryOrder->salesOrder->form->save();
                    }
    
                    // Insert User Activity
                    $this->_storeUserActivity('Cancellation Approved by Email', $deliveryOrder);
                    continue;    
                } 
                // approve close form
                if ($form->approval_status === 1 && $form->close_status === 0) {
                    $form->close_approval_by = $request->approver_id;
                    $form->close_approval_at = now();
                    $form->close_status = 1;
                    $form->done = 1;
                    $form->save();

                    if ($deliveryOrder->salesOrder) {
                        $deliveryOrder->salesOrder->form->done = true;
                        $deliveryOrder->salesOrder->form->save();
                    }
    
                    // Insert User Activity
                    $this->_storeUserActivity('Close Approved by Email', $deliveryOrder);
                    continue;    
                } 
                
                if (
                    $form->approval_status === 0 
                    && is_null($form->cancellation_status)
                    && is_null($form->close_status)
                ) {
                    try {
                        $deliveryOrder->checkQuantityOver($deliveryOrder->items);

                        $form->approval_by = $request->approver_id;
                        $form->approval_at = now();
                        $form->approval_status = 1;
                        $form->save();

                        $deliveryOrder->salesOrder->updateStatus();

                        $salesOrder = $deliveryOrder->salesOrder;
                        if ($salesOrder) {
                            $salesOrder->updateStatus();
                        }

                        $form->fireEventApprovedByEmail();
                    } catch (\Throwable $th) {
                        Log::error($form->number . ': ' . $th->getMessage());
                        $form->approval_notes = $th->getMessage();
                    }

                    continue;
                }
            }

            return new ApiResource($deliveryOrders);
        });

        return $result;
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request)
    {
        $this->request = $request;

        $result = DB::connection('tenant')->transaction(function () use ($request) {       
            $deliveryOrders = DeliveryOrder::whereIn('id', $request->ids)->get();
            
            foreach ($deliveryOrders as $deliveryOrder) {
                $form = $deliveryOrder->form;

                if ($form->cancellation_status === 0 && is_null($form->close_status)) {
                    $form->cancellation_approval_by = $request->approver_id;
                    $form->cancellation_approval_at = now();
                    $form->cancellation_approval_reason = $request->get('reason');
                    $form->cancellation_status = -1;
                    $form->save();
    
                    // Insert User Activity
                    $this->_storeUserActivity('Cancellation Rejected by Email', $deliveryOrder);
                    continue; 
                } 
                // approve close form
                if ($form->approval_status === 1 && $form->close_status === 0) {
                    $form->close_approval_by = $request->approver_id;
                    $form->close_approval_at = now();
                    $form->close_approval_reason = $request->get('reason');
                    $form->close_status = -1;
                    $form->save();
    
                    // Insert User Activity
                    $this->_storeUserActivity('Close Rejected by Email', $deliveryOrder);
                    continue;    
                } 
                if (
                    $form->approval_status === 0 
                    && is_null($form->cancellation_status)
                    && is_null($form->close_status)
                ) {
                    $form->approval_by = $request->approver_id;
                    $form->approval_at = now();
                    $form->approval_reason = $request->get('reason');
                    $form->approval_status = -1;
                    $form->save();
    
                    $form->fireEventRejectedByEmail();
                    continue;
                }
            }
    
            return new ApiResource($deliveryOrders);
        });

        return $result;
    }
}
