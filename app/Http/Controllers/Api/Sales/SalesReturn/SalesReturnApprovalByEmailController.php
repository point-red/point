<?php

namespace App\Http\Controllers\Api\Sales\SalesReturn;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;

use App\Model\UserActivity;
use App\Model\Sales\SalesReturn\SalesReturn;
use Exception;
use App\Model\Sales\SalesInvoice\SalesInvoiceReference;
use App\Model\Accounting\Journal;
use App\Model\Inventory\Inventory;

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
                try {
                    if ($salesReturn->form->approval_status === 1 && $salesReturn->form->cancellation_status === null) {
                        throw new Exception('form '.$salesReturn->form->number.' already approved', 422);
                    }
                } catch (\Throwable $th) {
                    return response_error($th);
                }
            }
            
            foreach ($salesReturns as $salesReturn) {  
                $form = $salesReturn->form;

                // approve cancellation form
                if ($form->cancellation_status === 0 && is_null($form->close_status)) {
                    if($form->approval_status === 1) {
                        SalesReturn::updateInvoiceQuantity($salesReturn, 'revert');
                        Inventory::where('form_id', $salesReturn->form->id)->delete();
                        Journal::where('form_id', $salesReturn->form->id)->orWhere('form_id_reference', $salesReturn->form->id)->delete();
                        SalesInvoiceReference::where('sales_invoice_id', $salesReturn->sales_invoice_id)
                            ->where('referenceable_id', $salesReturn->id)
                            ->where('referenceable_type', 'SalesReturn')->delete();
                    }

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
                    SalesReturn::updateSalesInvoiceReference($salesReturn);

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
        $validated = $request->validate([ 'reason' => 'required|max:255' ]);
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
