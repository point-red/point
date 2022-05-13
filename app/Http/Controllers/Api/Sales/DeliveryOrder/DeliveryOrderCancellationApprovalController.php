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
        $deliveryOrder = DeliveryOrder::findOrFail($id);

        $deliveryOrder->form->cancellation_approval_by = auth()->user()->id;
        $deliveryOrder->form->cancellation_approval_at = now();
        $deliveryOrder->form->cancellation_status = 1;
        $deliveryOrder->form->save();

        if ($deliveryOrder->salesOrder) {
            $deliveryOrder->salesOrder->form->done = false;
            $deliveryOrder->salesOrder->form->save();
        }

        $deliveryOrder->form->fireEventCancelApproved();

        return new ApiResource($deliveryOrder);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $request->validate([ 'reason' => 'required ']);
        
        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $deliveryOrder->form->cancellation_approval_by = auth()->user()->id;
        $deliveryOrder->form->cancellation_approval_at = now();
        $deliveryOrder->form->cancellation_approval_reason = $request->get('reason');
        $deliveryOrder->form->cancellation_status = -1;
        $deliveryOrder->form->save();

        if ($deliveryOrder->salesOrder) {
            $deliveryOrder->salesOrder->form->done = false;
            $deliveryOrder->salesOrder->form->save();
        }

        $deliveryOrder->form->fireEventCancelRejected();

        return new ApiResource($deliveryOrder);
    }
}
