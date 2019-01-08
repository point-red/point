<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseOrder;

use App\Model\Form;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseReceive\PurchaseReceiveItem;
use Illuminate\Http\Request;
use App\Model\Master\Supplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;

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
            ->join(Form::getTableName(), PurchaseOrder::getTableName().'.id', '=', Form::getTableName().'.formable_id')
            ->join(Supplier::getTableName(), PurchaseOrder::getTableName().'.supplier_id', '=', Supplier::getTableName().'.id')
            ->select(PurchaseOrder::getTableName().'.*')
            ->where(Form::getTableName().'.formable_type', PurchaseOrder::class)
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

            return new ApiResource($purchaseOrder
                ->load('form')
                ->load('supplier')
                ->load('items.allocation')
                ->load('services.allocation')
            );
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

        $purchaseOrderItemIds = array_column($purchaseOrder->items->toArray(), 'id');

        $tempArray = PurchaseReceiveItem::whereIn('purchase_order_item_id', $purchaseOrderItemIds)
            ->join(PurchaseReceive::getTableName(), PurchaseReceive::getTableName().'.id', '=', 'purchase_receive_items.purchase_receive_id')
            ->join(Form::getTableName(), PurchaseReceive::getTableName().'.id', '=', Form::getTableName().'.formable_id')
            ->groupBy('purchase_order_item_id')
            ->select('purchase_receive_items.purchase_order_item_id')
            ->addSelect(\DB::raw('SUM(quantity) AS sum_received'))
            ->where(function($query) {
                $query->where(Form::getTableName().'.canceled', false)
                    ->orWhereNull(Form::getTableName().'.canceled');
            })->where(function($query) {
                $query->where(Form::getTableName().'.approved', true)
                    ->orWhereNull(Form::getTableName().'.approved');
            })->get();

        $quantityReceivedItems = [];
        foreach ($tempArray as $value) {
            $quantityReceivedItems[$value['purchase_order_item_id']] = $value['sum_received'];
        }

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
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $purchaseOrder->delete();

        return response()->json([], 204);
    }
}
