<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseContract;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseContract\PurchaseContract;
use Illuminate\Http\Request;

class PurchaseContractCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $purchaseContract = PurchaseContract::findOrFail($id);
        $purchaseContract->form->cancellation_approval_by = auth()->user()->id;
        $purchaseContract->form->cancellation_approval_at = now();
        $purchaseContract->form->cancellation_status = 1;
        $purchaseContract->form->save();

        return new ApiResource($purchaseContract);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $purchaseContract = PurchaseContract::findOrFail($id);
        $purchaseContract->form->cancellation_approval_by = auth()->user()->id;
        $purchaseContract->form->cancellation_approval_at = now();
        $purchaseContract->form->cancellation_approval_reason = $request->get('reason');
        $purchaseContract->form->cancellation_status = -1;
        $purchaseContract->form->save();

        return new ApiResource($purchaseContract);
    }
}
