<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\DeliveryOrder\DeliveryOrder\StoreDeliveryOrderRequest;
use App\Http\Requests\Sales\DeliveryOrder\DeliveryOrder\UpdateDeliveryOrderRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeliveryOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $deliveryOrders = DeliveryOrder::from(DeliveryOrder::getTableName().' as '.DeliveryOrder::$alias)->eloquentFilter($request);

        $deliveryOrders = DeliveryOrder::joins($deliveryOrders, $request->get('join'));

        $deliveryOrders = pagination($deliveryOrders, $request->get('limit'));

        return new ApiCollection($deliveryOrders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDeliveryOrderRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(StoreDeliveryOrderRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $deliveryOrder = DeliveryOrder::create($request->all());
            $deliveryOrder
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($deliveryOrder);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $deliveryOrder = DeliveryOrder::eloquentFilter($request)->findOrFail($id);

        if ($request->has('with_archives')) {
            $deliveryOrder->archives = $deliveryOrder->archives();
        }

        if ($request->get('remaining_info')) {
            $deliveryNotes = $deliveryOrder->deliveryNotes()->with('items')->get();

            foreach ($deliveryOrder->items as $deliveryOrderItem) {
                $deliveryOrderItem->quantity_pending = $deliveryOrderItem->quantity;

                foreach ($deliveryNotes as $deliveryNote) {
                    $deliveryNoteItem = $deliveryNote->items->firstWhere('delivery_order_item_id', $deliveryOrderItem->id);
                    if ($deliveryNoteItem) {
                        $deliveryOrderItem->quantity_pending -= $deliveryNoteItem->quantity;
                    }
                }
            }
        }

        return new ApiResource($deliveryOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDeliveryOrderRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateDeliveryOrderRequest $request, $id)
    {
        // TODO prevent delete if referenced by delivery order
        $deliveryOrder = DeliveryOrder::with('form')->findOrFail($id);

        $deliveryOrder->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $deliveryOrder) {
            $deliveryOrder->form->archive($request->notes);
            $request['number'] = $deliveryOrder->form->edited_number;
            $request['old_increment'] = $deliveryOrder->form->increment;

            $deliveryOrder = DeliveryOrder::create($request->all());
            $deliveryOrder->load(['form', 'customer', 'items']);

            return new ApiResource($deliveryOrder);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $deliveryOrder->isAllowedToDelete();

        $request->validate([ 'reason' => 'required ']);
        
        $response = $deliveryOrder->requestCancel($request);

        return response()->json([], 204);
    }
}
