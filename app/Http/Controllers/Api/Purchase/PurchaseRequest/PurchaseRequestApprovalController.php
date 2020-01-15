<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseRequest;

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

        $approvalMatch = null;

        if ($purchaseRequest->form->approvals->count() == 0) {
            // approval not found exception
        }

        foreach ($purchaseRequest->form->approvals as $approval) {
            if (!auth()->user()) {
                if ($request->get('token') == $approval->token) {
                    $approvalMatch = $approval;
                    break;
                }
            }
            if ($approval->request_to == auth()->user()->id) {
                $approvalMatch = $approval;
                break;
            }
        }

        if ($approvalMatch == null) {
            // unauthorized exception
        } else {
            $purchaseRequest->form->approved = true;
            $purchaseRequest->form->save();
            $approvalMatch->approval_at = now();
            $approvalMatch->approved = true;
            $approvalMatch->save();
        }

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

        $approvalMatch = null;

        if ($purchaseRequest->form->approvals->count() == 0) {
            // approval not found exception
        }

        foreach ($purchaseRequest->form->approvals as $approval) {
            if (!auth()->user()) {
                if ($request->get('token') == $approval->token) {
                    $approvalMatch = $approval;
                    break;
                }
            }
            if ($approval->request_to == auth()->user()->id) {
                $approvalMatch = $approval;
                break;
            }
        }

        if ($approvalMatch == null) {
            // unauthorized exception
        } else {
            $purchaseRequest->form->approved = false;
            $purchaseRequest->form->save();
            $approvalMatch->approval_at = now();
            $approvalMatch->approved = false;
            $approvalMatch->save();
        }

        return new ApiResource($purchaseRequest);
    }
}
