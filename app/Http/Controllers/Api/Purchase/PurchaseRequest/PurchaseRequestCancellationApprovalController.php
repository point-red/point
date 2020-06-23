<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseRequest;

use App\Contracts\Controller\CancellationApproval;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancellationApproval\ApproveRequest;
use App\Http\Requests\CancellationApproval\RejectRequest;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use Illuminate\Http\Request;

class PurchaseRequestCancellationApprovalController extends Controller implements CancellationApproval
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(ApproveRequest $request, $id)
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
    public function reject(RejectRequest $request, $id)
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
