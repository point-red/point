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
     * @throws UnauthorizedException
     * @throws ApprovalNotFoundException
     */
    public function approve(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);

        $approvalMatch = null;

        if ($purchaseRequest->form->approvals->count() == 0) {
            throw new ApprovalNotFoundException();
        }

        foreach ($purchaseRequest->form->approvals as $approval) {
            if (!auth()->user()) {
                if ($request->get('token') == $approval->token) {
                    $approvalMatch = $approval;
                    break;
                }
            }
            if ($approval->requested_to == auth()->user()->id) {
                $approvalMatch = $approval;
                break;
            }
        }

        if ($approvalMatch == null) {
            throw new UnauthorizedException();
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
     * @throws ApprovalNotFoundException
     * @throws UnauthorizedException
     */
    public function reject(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);

        $approvalMatch = null;

        if ($purchaseRequest->form->approvals->count() == 0) {
            throw new ApprovalNotFoundException();
        }

        foreach ($purchaseRequest->form->approvals as $approval) {
            if (!auth()->user()) {
                if ($request->get('token') == $approval->token) {
                    $approvalMatch = $approval;
                    $approval->reason = $request->get('reason');
                    $approval->save();
                    break;
                }
            }
            if ($approval->requested_to == auth()->user()->id) {
                $approvalMatch = $approval;
                $approval->reason = $request->get('reason');
                $approval->save();
                break;
            }
        }

        if ($approvalMatch == null) {
            throw new UnauthorizedException();
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
