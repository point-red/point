<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Token;
use App\Model\UserActivity;
use App\Model\Master\Item;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Helpers\Inventory\InventoryHelper;
use App\Helpers\Journal\JournalHelper;

class DeliveryOrderApprovalByEmailController extends Controller
{
    protected $request;

    protected function _storeUserActivity($activity, $deliveryOrder) {
        $userActivity = new UserActivity;
        $userActivity->table_type = 'forms';
        $userActivity->table_id = $deliveryOrder->form->id;
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
                if ($form->approval_status === 0 && $form->cancellation_status === 0) {
                    $form->cancellation_approval_by = $request->approver_id;
                    $form->cancellation_approval_at = now();
                    $form->cancellation_status = 1;
                    $form->save();
    
                    // Insert User Activity
                    $this->_storeUserActivity('Cancellation Approved by Email', $deliveryOrder);
                    continue;    
                } 
                // approve close form
                if ($form->approval_status === 0 && $form->close_status === 0) {
                    $form->close_approval_by = $request->approver_id;
                    $form->close_approval_at = now();
                    $form->close_status = 1;
                    $form->save();
    
                    // Insert User Activity
                    $this->_storeUserActivity('Close Approved by Email', $deliveryOrder);
                    continue;    
                } 
                
                if ($form->approval_status === 0) {
                    $form->approval_by = $request->approver_id;
                    $form->approval_at = now();
                    $form->approval_status = 1;
                    $form->save();

                    $form->fireEventApprovedByEmail();
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

                if ($form->approval_status === 0 && $form->cancellation_status === 0) {
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
                if ($form->approval_status === 0 && $form->close_status === 0) {
                    $form->close_approval_by = $request->approver_id;
                    $form->close_approval_at = now();
                    $form->close_approval_reason = $request->get('reason');
                    $form->close_status = -1;
                    $form->save();
    
                    // Insert User Activity
                    $this->_storeUserActivity('Close Rejected by Email', $deliveryOrder);
                    continue;    
                } 
                if ($form->approval_status === 0) {
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
