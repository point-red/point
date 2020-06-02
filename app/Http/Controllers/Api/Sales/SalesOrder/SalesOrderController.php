<?php

namespace App\Http\Controllers\Api\Sales\SalesOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesOrder\SalesOrder\StoreSalesOrderRequest;
use App\Http\Requests\Sales\SalesOrder\SalesOrder\UpdateSalesOrderRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Sales\SalesOrder\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $salesOrders = SalesOrder::from(SalesOrder::getTableName().' as '.SalesOrder::$alias)->eloquentFilter($request);

        $salesOrders = SalesOrder::joins($salesOrders, $request->get('join'));

        $salesOrders = pagination($salesOrders, $request->get('limit'));

        return new ApiCollection($salesOrders);
    }

    /**
     * Store a newly created resource in storage.
     * Request :
     *  - number (String)
     *  - date (String YYYY-MM-DD hh:mm:ss)
     *  - sales_request_id (Int, Optional)
     *  - sales_contract_id (Int, Optional)
     *  - customer_id (Int)
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
     * @param StoreSalesOrderRequest $request
     * @return ApiResource
     * @throws Throwable
     */
    public function store(StoreSalesOrderRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $salesOrder = SalesOrder::create($request->all());
            $salesOrder
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($salesOrder);
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
        $salesOrder = SalesOrder::from(SalesOrder::getTableName().' as '.SalesOrder::$alias)->eloquentFilter($request);

        $salesOrder = SalesOrder::joins($salesOrder, $request->get('join'));

        $salesOrder = $salesOrder->where(SalesOrder::$alias.'.id', $id)->first();

        /*
         * anything except 0 is considered true, including string "false"
         */
        if ($request->get('remaining_info')) {
            $salesReceives = $salesOrder->salesReceives()->with('items')->get();

            foreach ($salesOrder->items as $orderItem) {
                $orderItem->quantity_pending = $orderItem->quantity;

                foreach ($salesReceives as $receive) {
                    $receiveItem = $receive->items->firstWhere('sales_order_item_id', $orderItem->id);
                    if ($receiveItem) {
                        $orderItem->quantity_pending -= $receiveItem->quantity;
                    }
                }
            }
        }

        return new ApiResource($salesOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSalesOrderRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateSalesOrderRequest $request, $id)
    {
        $salesOrder = SalesOrder::findOrFail($id);
        $salesOrder->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $salesOrder) {
            $salesOrder->form->archive();
            $request['number'] = $salesOrder->form->edited_number;
            $request['old_increment'] = $salesOrder->form->increment;

            $salesOrder = SalesOrder::create($request->all());
            $salesOrder
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($salesOrder);
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

        $salesOrder = SalesOrder::findOrFail($id);
        $salesOrder->isAllowedToDelete();
        $salesOrder->requestCancel($request);

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
