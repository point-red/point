<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\PurchaseOrder\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\Purchase\PurchaseOrder\PurchaseOrder\UpdatePurchaseOrderRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

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
        $purchaseOrders = PurchaseOrder::from(PurchaseOrder::getTableName().' as '.PurchaseOrder::$alias)->eloquentFilter($request);

        $purchaseOrders = PurchaseOrder::joins($purchaseOrders, $request->get('join'));

        $purchaseOrders = pagination($purchaseOrders, $request->get('limit'));

        return new ApiCollection($purchaseOrders);
    }

    /**
     * Store a newly created resource in storage.
     * Request :
     *  - number (String)
     *  - date (String YYYY-MM-DD hh:mm:ss)
     *  - purchase_request_id (Int, Optional)
     *  - purchase_contract_id (Int, Optional)
     *  - supplier_id (Int)
     *  - warehouse_id (Int, Optional)
     *  - eta (Date)
     *  - cash_only (Boolean, Optional)
     *  - need_down_payment (Decimal, Optional, Default 0)
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
     *      - allocation_id (Int, Optional).
     *
     * @param StorePurchaseOrderRequest $request
     * @return ApiResource
     * @throws Throwable
     */
    public function store(StorePurchaseOrderRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $purchaseOrder = PurchaseOrder::create($request->all());
            $purchaseOrder
                ->load('form')
                ->load('supplier')
                ->load('items.item')
                ->load('items.allocation');

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
        $purchaseOrder = PurchaseOrder::from(PurchaseOrder::getTableName().' as '.PurchaseOrder::$alias)->eloquentFilter($request);

        $purchaseOrder = PurchaseOrder::joins($purchaseOrder, $request->get('join'));

        $purchaseOrder = $purchaseOrder->where(PurchaseOrder::$alias.'.id', $id)->first();

        /*
         * anything except 0 is considered true, including string "false"
         */
        if ($request->get('remaining_info')) {
            $purchaseReceives = $purchaseOrder->purchaseReceives()->with('items')->get();

            foreach ($purchaseOrder->items as $orderItem) {
                $orderItem->quantity_pending = $orderItem->quantity;

                foreach ($purchaseReceives as $receive) {
                    $receiveItem = $receive->items->firstWhere('purchase_order_item_id', $orderItem->id);
                    if ($receiveItem) {
                        $orderItem->quantity_pending -= $receiveItem->quantity;
                    }
                }
            }
        }

        return new ApiResource($purchaseOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePurchaseOrderRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdatePurchaseOrderRequest $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $purchaseOrder) {
            $purchaseOrder->form->archive();
            $request['number'] = $purchaseOrder->form->edited_number;
            $request['old_increment'] = $purchaseOrder->form->increment;

            $purchaseOrder = PurchaseOrder::create($request->all());
            $purchaseOrder
                ->load('form')
                ->load('supplier')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($purchaseOrder);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->isAllowedToDelete();
        $purchaseOrder->requestCancel($request);

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
