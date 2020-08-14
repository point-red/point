<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseReceive;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReceiveCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();
        $purchaseReceive = PurchaseReceive::findOrFail($id);
        $purchaseReceive->form->cancellation_approval_by = auth()->user()->id;
        $purchaseReceive->form->cancellation_approval_at = now();
        $purchaseReceive->form->cancellation_status = 1;
        $purchaseReceive->form->save();

        // Undone purchase order form
        foreach ($purchaseReceive->items as $purchaseReceiveItem) {
            $purchaseReceiveItem->purchaseOrderItem->purchaseOrder->form->done = false;
            $purchaseReceiveItem->purchaseOrderItem->purchaseOrder->form->save();
        }
        DB::connection('tenant')->commit();
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
