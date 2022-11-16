<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->form->approval_by = auth()->user()->id;
        $purchaseOrder->form->approval_at = now();
        $purchaseOrder->form->approval_status = 1;
        $purchaseOrder->form->save();

        $purchaseOrder->form->fireEventApproved();

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
        $purchaseOrder->form->approval_by = auth()->user()->id;
        $purchaseOrder->form->approval_at = now();
        $purchaseOrder->form->approval_reason = $request->get('reason');
        $purchaseOrder->form->approval_status = -1;
        $purchaseOrder->form->save();

        $purchaseOrder->form->fireEventRejected();

        return new ApiResource($purchaseOrder);
    }
}
