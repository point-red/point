<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->form->cancellation_approval_by = auth()->user()->id;
        $purchaseOrder->form->cancellation_approval_at = now();
        $purchaseOrder->form->cancellation_status = 1;
        $purchaseOrder->form->save();

        $purchaseOrder->updateReference();

        $purchaseOrder->form->fireEventCancelApproved();

        DB::connection('tenant')->commit();

        return new ApiResource($purchaseOrder);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->form->cancellation_approval_by = auth()->user()->id;
        $purchaseOrder->form->cancellation_approval_at = now();
        $purchaseOrder->form->cancellation_approval_reason = $request->get('reason');
        $purchaseOrder->form->cancellation_status = -1;
        $purchaseOrder->form->save();

        $purchaseOrder->form->fireEventCancelRejected();

        return new ApiResource($purchaseOrder);
    }
}
