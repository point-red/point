<?php

namespace App\Http\Controllers\Api\Sales\SalesOrder;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Customer;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\DeliveryOrder\DeliveryOrderItem;
use App\Model\Sales\SalesOrder\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $salesOrders = SalesOrder::eloquentFilter($request)
            ->joinForm()
            ->join(Customer::getTableName(), SalesOrder::getTableName('customer_id'), '=', Customer::getTableName('id'))
            ->select(SalesOrder::getTableName('*'))
            ->notArchived()
            ->with('form');

        $salesOrders = pagination($salesOrders, $request->get('limit'));

        return new ApiCollection($salesOrders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $salesOrder = SalesOrder::create($request->all());
            $salesOrder
                ->load('form')
                ->load('customer')
                ->load('items.allocation')
                ->load('services.allocation');

            return new ApiResource($salesOrder);
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
        $salesOrder = SalesOrder::eloquentFilter($request)
            ->with('form')
            ->with('salesQuotation')
            ->with('warehouse')
            ->with('customer')
            ->with('items.item')
            ->with('items.allocation')
            ->with('services.service')
            ->with('services.allocation')
            ->findOrFail($id);

        $salesOrderItemIds = $salesOrder->items->pluck('id');

        $tempArray = DeliveryOrder::joinForm()
            ->join(DeliveryOrderItem::getTableName(), DeliveryOrder::getTableName('id'), '=', DeliveryOrderItem::getTableName('delivery_order_id'))
            ->groupBy('sales_order_item_id')
            ->select(DeliveryOrderItem::getTableName('sales_order_item_id'))
            ->addSelect(\DB::raw('SUM(quantity) AS sum_delivered'))
            ->whereIn('sales_order_item_id', $salesOrderItemIds)
            ->active()
            ->get();

        $quantityDeliveredItems = $tempArray->pluck('sum_delivered', 'sales_order_item_id');

        foreach ($salesOrder->items as $salesOrderItem) {
            $quantityDelivered = $quantityDeliveredItems[$salesOrderItem->id] ?? 0;
            $salesOrderItem->quantity_pending = $salesOrderItem->quantity - $quantityDelivered;
        }

        return new ApiResource($salesOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $salesOrder = SalesOrder::findOrFail($id);

        $salesOrder->delete();

        return response()->json([], 204);
    }
}
