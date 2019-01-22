<?php

namespace App\Http\Controllers\Api\Sales\DeliveryNote;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $deliveryNote = DeliveryNote::eloquentFilter($request)
            ->join(Customer::getTableName(), DeliveryNote::getTableName('customer_id'), '=', Customer::getTableName('id'))
            ->select(DeliveryNote::getTableName('*'))
            ->joinForm()
            ->notArchived()
            ->with('form');

        $deliveryNote = pagination($deliveryNote, $request->get('limit'));

        return new ApiCollection($deliveryNote);
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
            $deliveryNote = DeliveryNote::create($request->all());
            $deliveryNote
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

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

            return new ApiResource($deliveryNote);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $deliveryNote = DeliveryNote::eloquentFilter($request)
            ->with('form')
            ->with('deliveryOrder.form')
            ->with('warehouse')
            ->with('customer')
            ->with('items.item')
            ->with('items.allocation')
            ->findOrFail($id);

        return new ApiResource($deliveryNote);
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
        // TODO prevent delete if referenced by sales invoice
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {

            $salesInvoice = SalesInvoice::findOrFail($id);

            $newSalesInvoice = $salesInvoice->edit($request->all());

            return new ApiResource($newSalesInvoice);
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
        $deliveryNote = DeliveryNote::findOrFail($id);

        $deliveryNote->delete();

        return response()->json([], 204);
    }
}
