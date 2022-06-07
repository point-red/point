<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;

class DeliveryOrderCloseApprovalController extends Controller
{
    /**
     * Close the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     */
    public function close(Request $request, $id)
    {
        try {
            $request->validate([ 'reason' => 'required' ]);
            
            $deliveryOrder = DeliveryOrder::findOrFail($id);
            
            if ($deliveryOrder->form->done != 0) throw new Exception("form not in pending state", 422);
    
            $deliveryOrder->requestClose($request);
        } catch (\Throwable $th) {
            return response_error($th);
        }

        return response()->json([], 204);
    }
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);

        $result = DB::connection('tenant')->transaction(function () use ($request, $deliveryOrder) {
            try {
                if($deliveryOrder->form->done === 0 && $deliveryOrder->form->close_status !== 0) {
                    throw new Exception("form not in close pending state", 422);
                }
    
                $deliveryOrder->form->close_approval_by = auth()->user()->id;
                $deliveryOrder->form->close_approval_at = now();
                $deliveryOrder->form->close_status = true;
                $deliveryOrder->form->done = true;
                $deliveryOrder->form->save();
    
                if ($deliveryOrder->salesOrder) {
                    $deliveryOrder->salesOrder->form->done = true;
                    $deliveryOrder->salesOrder->form->save();
                }
    
                $deliveryOrder->form->fireEventCloseApproved();
            } catch (\Throwable $th) {
                return response_error($th);
            }
    
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
        $request->validate([ 'reason' => 'required ']);
        
        $deliveryOrder = DeliveryOrder::findOrFail($id);

        $result = DB::connection('tenant')->transaction(function () use ($request, $deliveryOrder) {
            try {
                if($deliveryOrder->form->done === 0 && $deliveryOrder->form->close_status !== 0) {
                    throw new Exception("form not in close pending state", 422);
                }
    
                $deliveryOrder->form->close_approval_by = auth()->user()->id;
                $deliveryOrder->form->close_approval_at = now();
                $deliveryOrder->form->close_approval_reason = $request->get('reason');
                $deliveryOrder->form->close_status = -1;
                $deliveryOrder->form->save();
    
                $deliveryOrder->form->fireEventCloseRejected();
            } catch (\Throwable $th) {
                return response_error($th);
            }
    
            return new ApiResource($deliveryOrder);
        });

        return $result;
    }
}
