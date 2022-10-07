<?php

namespace App\Http\Controllers\Api\Sales\DeliveryNote;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryNoteApprovalController extends Controller
{
    /**
     * @param  Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($id) {
            $deliveryNote = DeliveryNote::findOrFail($id);

            $form = $deliveryNote->form;
            $form->approval_by = auth()->user()->id;
            $form->approval_at = now();
            $form->approval_status = 1;
            $form->save();

            $deliveryOrder = $deliveryNote->deliveryOrder;
            if ($deliveryOrder) {
                $deliveryOrder->updateStatus();
            }

            DeliveryNote::updateInventory($deliveryNote);
            DeliveryNote::updateJournal($deliveryNote);

            $form->fireEventApproved();

            return new ApiResource($deliveryNote);
        });

        return $result;
    }

    /**
     * @param  Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate(['reason' => 'required']);

        $result = DB::connection('tenant')->transaction(function () use ($validated, $id) {
            $deliveryNote = DeliveryNote::findOrFail($id);
            $deliveryNote->form->approval_by = auth()->user()->id;
            $deliveryNote->form->approval_at = now();
            $deliveryNote->form->approval_reason = $validated['reason'];
            $deliveryNote->form->approval_status = -1;
            $deliveryNote->form->save();

            $deliveryNote->form->fireEventRejected();

            return new ApiResource($deliveryNote);
        });

        return $result;
    }
}
