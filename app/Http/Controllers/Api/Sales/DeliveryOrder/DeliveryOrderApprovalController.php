<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use DB;
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
        
        $result = DB::connection('tenant')->transaction(function () use ($id) {
            $deliveryOrder = DeliveryOrder::findOrFail($id);

            $form = $deliveryOrder->form;
            $form->approval_by = auth()->user()->id;
            $form->approval_at = now();
            $form->approval_status = 1;
            $form->save();
    
            $salesOrder = $deliveryOrder->salesOrder;
            if ($salesOrder) {
                $salesOrder->updateStatus();
            }

            $form->fireEventApproved();
    
            return new ApiResource($deliveryOrder);
        });
        

        return $result;
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
