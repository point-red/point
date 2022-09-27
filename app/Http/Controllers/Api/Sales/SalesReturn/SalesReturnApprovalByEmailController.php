<?php

namespace App\Http\Controllers\Api\Sales\SalesReturn;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;

use App\Model\UserActivity;
use App\Model\Sales\SalesReturn\SalesReturn;

class SalesReturnApprovalByEmailController extends Controller
{
    protected $request;

    protected function _storeUserActivity($activity, $salesReturn) {
        $userActivity = new UserActivity;
        $userActivity->table_type = $salesReturn::$morphName;
        $userActivity->table_id = $salesReturn->id;
        $userActivity->number = $salesReturn->form->number;
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
            $salesReturns = SalesReturn::whereIn('id', $request->ids)->get();
            
            foreach ($salesReturns as $salesReturn) {  
                $form = $salesReturn->form;

                // approve cancellation form
                if ($form->cancellation_status === 0 && is_null($form->close_status)) {
                    $form->cancellation_approval_by = $request->approver_id;
                    $form->cancellation_approval_at = now();
                    $form->cancellation_status = 1;
                    $form->save();
    
                    // Insert User Activity
                    $this->_storeUserActivity('Cancellation Approved by Email', $salesReturn);
                    continue;    
                } 
                
                if (
                    $form->approval_status === 0 
                    && is_null($form->cancellation_status)
                    && is_null($form->close_status)
                ) {
                    $salesReturn->checkQuantity($salesReturn->items);

                    $form->approval_by = $request->approver_id;
                    $form->approval_at = now();
                    $form->approval_status = 1;
                    $form->save();

                    SalesReturn::updateJournal($salesReturn);
                    SalesReturn::updateInventory($salesReturn->form, $salesReturn);
                    SalesReturn::updateInvoiceQuantity($salesReturn, 'update');

                    $form->fireEventApprovedByEmail();

                    continue;
                }
            }

            return new ApiResource($salesReturns);
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
            $salesReturns = SalesReturn::whereIn('id', $request->ids)->get();
            
            foreach ($salesReturns as $salesReturn) {
                $form = $salesReturn->form;

                if ($form->cancellation_status === 0 && is_null($form->close_status)) {
                    $form->cancellation_approval_by = $request->approver_id;
                    $form->cancellation_approval_at = now();
                    $form->cancellation_approval_reason = $request->get('reason');
                    $form->cancellation_status = -1;
                    $form->save();
    
                    // Insert User Activity
                    $this->_storeUserActivity('Cancellation Rejected by Email', $salesReturn);
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
    
            return new ApiResource($salesReturns);
        });

        return $result;
    }
}
