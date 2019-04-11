<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use Illuminate\Http\Request;
use App\Model\Master\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\DeliveryNote\DeliveryNoteItem;

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
        $deliverOrders = DeliveryOrder::eloquentFilter($request);

        $deliverOrders = pagination($deliverOrders, $request->get('limit'));

        return new ApiCollection($deliverOrders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(Request $request)
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
        $deliveryOrder = DeliveryOrder::eloquentFilter($request)
            ->with('form')
            ->with('salesOrder.form')
            ->with('warehouse')
            ->with('customer')
            ->with('items.item')
            ->with('items.allocation')
            ->findOrFail($id);

        $deliveryOrderItemIds = $deliveryOrder->items->pluck('id');

        $tempArray = DeliveryNote::active()
            ->join(DeliveryNoteItem::getTableName(), DeliveryNote::getTableName('id'), '=', DeliveryNoteItem::getTableName('delivery_note_id'))
            ->groupBy('delivery_order_item_id')
            ->select(DeliveryNoteItem::getTableName('delivery_order_item_id'))
            ->addSelect(\DB::raw('SUM(quantity) AS sum_delivered'))
            ->whereIn('delivery_order_item_id', $deliveryOrderItemIds)
            ->get();

        $quantityDeliveredItems = $tempArray->pluck('sum_delivered', 'delivery_order_item_id');

        foreach ($deliveryOrder->items as $deliveryOrderItem) {
            $quantityDelivered = $quantityDeliveredItems[$deliveryOrderItem->id] ?? 0;
            $deliveryOrderItem->quantity_pending = $deliveryOrderItem->quantity - $quantityDelivered;
        }

        return new ApiResource($deliveryOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int  $id
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        // TODO prevent delete if referenced by delivery notes
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $deliveryOrder = DeliveryOrder::findOrFail($id);

            $newDeliveryOrder = $deliveryOrder->edit($request->all());

            return new ApiResource($newDeliveryOrder);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);

        $deliveryOrder->delete();

        return response()->json([], 204);
    }
}
