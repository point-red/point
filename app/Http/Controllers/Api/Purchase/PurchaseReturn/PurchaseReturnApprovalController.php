<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseReturn;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseReturn\PurchaseReturn;
use Illuminate\Http\Request;

class PurchaseReturnApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $purchaseReturn = PurchaseReturn::findOrFail($id);
        $purchaseReturn->form->approval_by = auth()->user()->id;
        $purchaseReturn->form->approval_at = now();
        $purchaseReturn->form->approval_status = 1;
        $purchaseReturn->form->save();

        return new ApiResource($purchaseReturn);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $purchaseReturn = PurchaseReturn::findOrFail($id);
        $purchaseReturn->form->approval_by = auth()->user()->id;
        $purchaseReturn->form->approval_at = now();
        $purchaseReturn->form->approval_reason = $request->get('reason');
        $purchaseReturn->form->approval_status = -1;
        $purchaseReturn->form->save();

        return new ApiResource($purchaseReturn);
    }
}
