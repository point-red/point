<?php

namespace App\Http\Controllers\Api\Sales\SalesReturn;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Accounting\Journal;
use App\Model\Inventory\Inventory;

class SalesReturnCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $salesReturn = SalesReturn::findOrFail($id);
        
        $result = DB::connection('tenant')->transaction(function () use ($request, $salesReturn) {
            try {
                $salesReturn->isAllowedToUpdate();
                if($salesReturn->form->cancellation_status !== 0) {
                    throw new Exception("form not in cancellation pending state", 422);
                }
                if($salesReturn->form->approval_status === 1) {
                    SalesReturn::updateInvoiceQuantity($salesReturn, 'revert');
                    Inventory::where('form_id', $salesReturn->form->id)->delete();
                    Journal::where('form_id', $salesReturn->form->id)->orWhere('form_id_reference', $salesReturn->form->id)->delete();
                }
    
                $salesReturn->form->cancellation_approval_by = auth()->user()->id;
                $salesReturn->form->cancellation_approval_at = now();
                $salesReturn->form->cancellation_status = 1;
                $salesReturn->form->save();
    
                $salesReturn->form->fireEventCancelApproved();
            } catch (\Throwable $th) {
                return response_error($th);
            }
    
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
        $request->validate([ 'reason' => 'required ']);
        
        $salesReturn = SalesReturn::findOrFail($id);

        $result = DB::connection('tenant')->transaction(function () use ($request, $salesReturn) {
            try {
                $salesReturn->isAllowedToUpdate();
                if($salesReturn->form->cancellation_status !== 0) {
                    throw new Exception("form not in cancellation pending state", 422);
                }
    
                $salesReturn->form->cancellation_approval_by = auth()->user()->id;
                $salesReturn->form->cancellation_approval_at = now();
                $salesReturn->form->cancellation_approval_reason = $request->get('reason');
                $salesReturn->form->cancellation_status = -1;
                $salesReturn->form->save();
    
                $salesReturn->form->fireEventCancelRejected();
            } catch (\Throwable $th) {
                return response_error($th);
            }
    
            return new ApiResource($salesReturn);
        });

        return $result;
    }
}
