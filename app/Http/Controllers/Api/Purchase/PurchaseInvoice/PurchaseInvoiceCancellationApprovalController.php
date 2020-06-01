<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseInvoice;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use Illuminate\Http\Request;

class PurchaseInvoiceCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        $purchaseInvoice->form->cancellation_approval_by = auth()->user()->id;
        $purchaseInvoice->form->cancellation_approval_at = now();
        $purchaseInvoice->form->cancellation_status = 1;
        $purchaseInvoice->form->save();

        return new ApiResource($purchaseInvoice);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        $purchaseInvoice->form->cancellation_approval_by = auth()->user()->id;
        $purchaseInvoice->form->cancellation_approval_at = now();
        $purchaseInvoice->form->cancellation_approval_reason = $request->get('reason');
        $purchaseInvoice->form->cancellation_status = -1;
        $purchaseInvoice->form->save();

        return new ApiResource($purchaseInvoice);
    }
}
