<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseDownPayment;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use Illuminate\Http\Request;

class PurchaseDownPaymentApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $purchaseDownPayment = PurchaseDownPayment::findOrFail($id);
        $purchaseDownPayment->form->approval_by = auth()->user()->id;
        $purchaseDownPayment->form->approval_at = now();
        $purchaseDownPayment->form->approval_status = 1;
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
        $purchaseDownPayment->form->approval_by = auth()->user()->id;
        $purchaseDownPayment->form->approval_at = now();
        $purchaseDownPayment->form->approval_reason = $request->get('reason');
        $purchaseDownPayment->form->approval_status = -1;
        $purchaseDownPayment->form->save();

        return new ApiResource($purchaseDownPayment);
    }
}
