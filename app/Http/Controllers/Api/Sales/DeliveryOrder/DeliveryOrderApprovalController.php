<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use Illuminate\Http\Request;

class DeliveryOrderApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $deliveryOrder->form->approval_by = auth()->user()->id;
        $deliveryOrder->form->approval_at = now();
        $deliveryOrder->form->approval_status = 1;
        $deliveryOrder->form->save();

        $deliveryOrder->form->fireEventApproved();

        return new ApiResource($deliveryOrder);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $deliveryOrder->form->approval_by = auth()->user()->id;
        $deliveryOrder->form->approval_at = now();
        $deliveryOrder->form->approval_reason = $request->get('reason');
        $deliveryOrder->form->approval_status = -1;
        $deliveryOrder->form->save();

        $deliveryOrder->form->fireEventRejected();

        return new ApiResource($deliveryOrder);
    }
}
