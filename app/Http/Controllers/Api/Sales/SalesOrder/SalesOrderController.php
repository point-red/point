<?php

namespace App\Http\Controllers\Api\Sales\SalesOrder;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesOrder\SalesOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\Sales\DeliveryOrder\DeliveryOrderItem;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;

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
            ->join(Form::getTableName(), SalesOrder::getTableName().'.id', '=', Form::getTableName().'.formable_id')
            ->join(Customer::getTableName(), SalesOrder::getTableName().'.customer_id', '=', Customer::getTableName().'.id')
            ->select(SalesOrder::getTableName().'.*')
            ->where(Form::getTableName().'.formable_type', SalesOrder::class)
            ->whereNotNull(Form::getTableName().'.number')
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

            return new ApiResource($salesOrder
                ->load('form')
                ->load('customer')
                ->load('items.allocation')
                ->load('services.allocation')
            );
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
        
            $salesOrderItemIds = array_column($salesOrder->items->toArray(), 'id');

            $tempArray = DeliveryOrderItem::whereIn('sales_order_item_id', $salesOrderItemIds)
                ->join(DeliveryOrder::getTableName(), DeliveryOrder::getTableName().'.id', '=', 'delivery_order_items.delivery_order_id')
                ->join(Form::getTableName(), DeliveryOrder::getTableName().'.id', '=', Form::getTableName().'.formable_id')
                ->groupBy('sales_order_item_id')
                ->select('delivery_order_items.sales_order_item_id')
                ->addSelect(\DB::raw('SUM(quantity) AS sum_delivered'))
                ->where(function($query) {
                    $query->where(Form::getTableName().'.canceled', false)
                        ->orWhereNull(Form::getTableName().'.canceled');
                })->where(function($query) {
                    $query->where(Form::getTableName().'.approved', true)
                        ->orWhereNull(Form::getTableName().'.approved');
                })->get();
    
            $quantityDeliveredItems = [];
    
            foreach ($tempArray as $value) {
                $quantityDeliveredItems[$value['sales_order_item_id']] = $value['sum_delivered'];
            }
    
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
