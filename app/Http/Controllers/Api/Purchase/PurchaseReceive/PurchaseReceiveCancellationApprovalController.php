<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseReceive;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use Illuminate\Http\Request;

class PurchaseReceiveCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $purchaseReceive = PurchaseReceive::findOrFail($id);
        $purchaseReceive->form->cancellation_approval_by = auth()->user()->id;
        $purchaseReceive->form->cancellation_approval_at = now();
        $purchaseReceive->form->cancellation_status = 1;
        $purchaseReceive->form->save();

        return new ApiResource($purchaseReceive);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $purchaseReceive = PurchaseReceive::findOrFail($id);
        $purchaseReceive->form->cancellation_approval_by = auth()->user()->id;
        $purchaseReceive->form->cancellation_approval_at = now();
        $purchaseReceive->form->cancellation_approval_reason = $request->get('reason');
        $purchaseReceive->form->cancellation_status = -1;
        $purchaseReceive->form->save();

        return new ApiResource($purchaseReceive);
    }
}
