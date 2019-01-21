<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseReceive\PurchaseReceiveItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::eloquentFilter($request)
            ->join(Supplier::getTableName(), PurchaseOrder::getTableName('supplier_id'), '=', Supplier::getTableName('id'))
            ->select(PurchaseOrder::getTableName('*'))
            ->joinForm()
            ->notArchived()
            ->with('form');

        $purchaseOrders = pagination($purchaseOrders, $request->get('limit'));

        return new ApiCollection($purchaseOrders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Request :
     *
     *  - number (String)
     *  - date (String YYYY-MM-DD hh:mm:ss)
     *  - purchase_request_id (Int, Optional)
     *  - purchase_contract_id (Int, Optional)
     *  - supplier_id (Int)
     *  - warehouse_id (Int, Optional)
     *  - eta (Date)
     *  - cash_only (Boolean, Optional)
     *  - need_down_payment (Boolean, Optional)
     *  - delivery_fee (Decimal, Optional)
     *  - discount_percent (Decimal, Optional)
     *  - discount_value (Decimal, Optional)
     *  - type_of_tax (String ['include', 'exclude', 'non'])
     *  - tax (Decimal)
     *  -
     *  - items (Array) :
     *      - item_id (Int)
     *      - quantity (Decimal)
     *      - unit (String)
     *      - converter (Decimal)
     *      - price (Decimal)
     *      - discount_percent (Decimal, Optional)
     *      - discount_value (Decimal, Optional)
     *      - taxable (Boolean, Optional)
     *      - description (String)
     *      - allocation_id (Int, Optional)
     *
     *  - services (Array) :
     *      - service_id (Int)
     *      - quantity (Decimal)
     *      - price (Decimal)
     *      - discount_percent (Decimal, Optional)
     *      - discount_value (Decimal, Optional)
     *      - taxable (Boolean, Optional)
     *      - description (String)
     *      - allocation_id (Int, Optional)
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Throwable
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $purchaseOrder = PurchaseOrder::create($request->all());
            $purchaseOrder
                ->load('form')
                ->load('supplier')
                ->load('items.item')
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($purchaseOrder);
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
        $purchaseOrder = PurchaseOrder::eloquentFilter($request)
            ->with('form')
            ->with('purchaseRequest')
            ->with('warehouse')
            ->with('supplier')
            ->with('items.item')
            ->with('items.allocation')
            ->with('services.service')
            ->with('services.allocation')
            ->findOrFail($id);

        $purchaseOrderItemIds = $purchaseOrder->items->pluck('id');

        $tempArray = PurchaseReceive::joinForm()
            ->join(PurchaseReceiveItem::getTableName(), PurchaseReceive::getTableName('id'), '=', PurchaseReceiveItem::getTableName('purchase_receive_id'))
            ->select(PurchaseReceiveItem::getTableName('purchase_order_item_id'))
            ->addSelect(\DB::raw('SUM(quantity) AS sum_received'))
            ->whereIn('purchase_order_item_id', $purchaseOrderItemIds)
            ->groupBy('purchase_order_item_id')
            ->active()
            ->get();

        $quantityReceivedItems = $tempArray->pluck('sum_received', 'purchase_order_item_id');

        foreach ($purchaseOrder->items as $key => $purchaseOrderItem) {
            $quantityReceived = $quantityReceivedItems[$purchaseOrderItem->id] ?? 0;
            $purchaseOrderItem->quantity_pending = $purchaseOrderItem->quantity - $quantityReceived;
        }

        return new ApiResource($purchaseOrder);
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
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $newPurchaseOrder = $purchaseOrder->edit($request);

        return new ApiResource($newPurchaseOrder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $purchaseOrder->delete();

        return response()->json([], 204);
    }
}
