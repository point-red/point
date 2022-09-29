<?php

namespace App\Http\Controllers\Api\Sales\DeliveryNote;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryNoteCancellationApprovalController extends Controller
{
    /**
     * @param  Request  $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);

        $result = DB::connection('tenant')->transaction(function () use ($deliveryNote) {
            try {
                $deliveryNote->isAllowedToUpdate();
                if ($deliveryNote->form->cancellation_status !== 0) {
                    throw new Exception('form not in cancellation pending state', 422);
                }

                $deliveryNote->form->cancellation_approval_by = auth()->user()->id;
                $deliveryNote->form->cancellation_approval_at = now();
                $deliveryNote->form->cancellation_status = 1;
                $deliveryNote->form->save();

                if ($deliveryNote->deliveryOrder) {
                    $deliveryNote->deliveryOrder->form->done = false;
                    $deliveryNote->deliveryOrder->form->save();
                }

                $deliveryNote->form->fireEventCancelApproved();
            } catch (\Throwable $th) {
                return response_error($th);
            }

            return new ApiResource($deliveryNote);
        });

        return $result;
    }

    /**
     * @param  Request  $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required']);

        $deliveryNote = DeliveryNote::findOrFail($id);

        $result = DB::connection('tenant')->transaction(function () use ($request, $deliveryNote) {
            try {
                $deliveryNote->isAllowedToUpdate();
                if ($deliveryNote->form->cancellation_status !== 0) {
                    throw new Exception('form not in cancellation pending state', 422);
                }

                $deliveryNote->form->cancellation_approval_by = auth()->user()->id;
                $deliveryNote->form->cancellation_approval_at = now();
                $deliveryNote->form->cancellation_approval_reason = $request->get('reason');
                $deliveryNote->form->cancellation_status = -1;
                $deliveryNote->form->save();
                $deliveryNote->form->fireEventCancelRejected();
            } catch (\Throwable $th) {
                return response_error($th);
            }

            return new ApiResource($deliveryNote);
        });

        return $result;
    }
}
