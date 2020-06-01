<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseRequest;

use App\Exceptions\ApprovalNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use Illuminate\Http\Request;

class PurchaseRequestApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $purchaseRequest->form->approval_by = auth()->user()->id;
        $purchaseRequest->form->approval_at = now();
        $purchaseRequest->form->approval_status = 1;
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
        $purchaseRequest->form->approval_by = auth()->user()->id;
        $purchaseRequest->form->approval_at = now();
        $purchaseRequest->form->approval_reason = $request->get('reason');
        $purchaseRequest->form->approval_status = -1;
        $purchaseRequest->form->save();

        return new ApiResource($purchaseRequest);
    }
}
