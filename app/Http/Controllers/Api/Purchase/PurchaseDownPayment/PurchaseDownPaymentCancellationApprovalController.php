<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseDownPayment;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use Illuminate\Http\Request;

class PurchaseDownPaymentCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $purchaseDownPayment = PurchaseDownPayment::findOrFail($id);
        $purchaseDownPayment->form->cancellation_approval_by = auth()->user()->id;
        $purchaseDownPayment->form->cancellation_approval_at = now();
        $purchaseDownPayment->form->cancellation_status = 1;
        $purchaseDownPayment->form->save();

        return new ApiResource($purchaseDownPayment);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $purchaseDownPayment = PurchaseDownPayment::findOrFail($id);
        $purchaseDownPayment->form->cancellation_approval_by = auth()->user()->id;
        $purchaseDownPayment->form->cancellation_approval_at = now();
        $purchaseDownPayment->form->cancellation_approval_reason = $request->get('reason');
        $purchaseDownPayment->form->cancellation_status = -1;
        $purchaseDownPayment->form->save();

        return new ApiResource($purchaseDownPayment);
    }
}
