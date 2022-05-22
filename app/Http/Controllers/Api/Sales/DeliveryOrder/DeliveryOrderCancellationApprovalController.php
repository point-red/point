<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use Exception;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;

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
        
        try {
            $deliveryOrder->isAllowedToUpdate();
            if($deliveryOrder->form->cancellation_status !== 0) {
                throw new Exception("form not in cancellation pending state", 1);
            }

            $deliveryOrder->form->cancellation_approval_by = auth()->user()->id;
            $deliveryOrder->form->cancellation_approval_at = now();
            $deliveryOrder->form->cancellation_status = 1;
            $deliveryOrder->form->save();

            if ($deliveryOrder->salesOrder) {
                $deliveryOrder->salesOrder->form->done = false;
                $deliveryOrder->salesOrder->form->save();
            }

            $deliveryOrder->form->fireEventCancelApproved();
        } catch (\Throwable $th) {
            return response(['code' => $th->getCode(), 'message' => $th->getMessage()], 422);
        }

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

        try {
            $deliveryOrder->isAllowedToUpdate();
            if($deliveryOrder->form->cancellation_status !== 0) {
                throw new Exception("form not in cancellation pending state", 1);
            }

            $deliveryOrder->form->cancellation_approval_by = auth()->user()->id;
            $deliveryOrder->form->cancellation_approval_at = now();
            $deliveryOrder->form->cancellation_approval_reason = $request->get('reason');
            $deliveryOrder->form->cancellation_status = -1;
            $deliveryOrder->form->save();

            $deliveryOrder->form->fireEventCancelRejected();
        } catch (\Throwable $th) {
            return response(['code' => $th->getCode(), 'message' => $th->getMessage()], 422);
        }

        return new ApiResource($deliveryOrder);
    }
}
