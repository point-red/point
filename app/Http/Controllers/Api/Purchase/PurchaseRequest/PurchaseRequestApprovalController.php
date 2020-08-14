<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseRequest;

use App\Contracts\Controller\Approval;
use App\Http\Controllers\Controller;
use App\Http\Requests\Approval\ApproveRequest;
use App\Http\Requests\Approval\RejectRequest;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;

class PurchaseRequestApprovalController extends Controller implements Approval
{
    /**
     * @param ApproveRequest $request
     * @param $id
     * @return ApiResource
     */
    public function approve(ApproveRequest $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $purchaseRequest->form->approval_by = auth()->user()->id;
        $purchaseRequest->form->approval_at = now();
        $purchaseRequest->form->approval_status = 1;
        $purchaseRequest->form->save();

        return new ApiResource($purchaseRequest);
    }

    /**
     * @param RejectRequest $request
     * @param $id
     * @return ApiResource
     */
    public function reject(RejectRequest $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $purchaseRequest->form->approval_by = auth()->user()->id;
        $purchaseRequest->form->approval_at = now();
        $purchaseRequest->form->approval_reason = $request->get('reason');
        $purchaseRequest->form->approval_status = -1;
        $purchaseRequest->form->save();

        return new ApiResource($purchaseRequest);
    }
}
