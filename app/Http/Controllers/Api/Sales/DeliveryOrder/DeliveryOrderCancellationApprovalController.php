<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use Illuminate\Http\Request;

class DeliveryOrderCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $salesOrder = DeliveryOrder::findOrFail($id);
        $salesOrder->form->cancellation_approval_by = auth()->user()->id;
        $salesOrder->form->cancellation_approval_at = now();
        $salesOrder->form->cancellation_status = 1;
        $salesOrder->form->save();

        return new ApiResource($salesOrder);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $salesOrder = DeliveryOrder::findOrFail($id);
        $salesOrder->form->cancellation_approval_by = auth()->user()->id;
        $salesOrder->form->cancellation_approval_at = now();
        $salesOrder->form->cancellation_approval_reason = $request->get('reason');
        $salesOrder->form->cancellation_status = -1;
        $salesOrder->form->save();

        return new ApiResource($salesOrder);
    }
}
