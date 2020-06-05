<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseRequest;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use Illuminate\Http\Request;

class PurchaseRequestCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $purchaseRequest->form->cancellation_approval_by = auth()->user()->id;
        $purchaseRequest->form->cancellation_approval_at = now();
        $purchaseRequest->form->cancellation_status = 1;
        $purchaseRequest->form->save();

        return new ApiResource($purchaseRequest);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $purchaseRequest->form->cancellation_approval_by = auth()->user()->id;
        $purchaseRequest->form->cancellation_approval_at = now();
        $purchaseRequest->form->cancellation_approval_reason = $request->get('reason');
        $purchaseRequest->form->cancellation_status = -1;
        $purchaseRequest->form->save();

        return new ApiResource($purchaseRequest);
    }
}
