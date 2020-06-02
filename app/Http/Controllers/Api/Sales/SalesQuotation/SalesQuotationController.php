<?php

namespace App\Http\Controllers\Api\Sales\SalesQuotation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesQuotation\SalesQuotation\StoreSalesQuotationRequest;
use App\Http\Requests\Sales\SalesQuotation\SalesQuotation\UpdateSalesQuotationRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Sales\SalesQuotation\SalesQuotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SalesQuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $salesQuotations = SalesQuotation::from(SalesQuotation::getTableName().' as '.SalesQuotation::$alias)->eloquentFilter($request);

        $salesQuotations = SalesQuotation::joins($salesQuotations, $request->get('join'));

        $salesQuotations = pagination($salesQuotations, $request->get('limit'));

        return new ApiCollection($salesQuotations);
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
     * @param StoreSalesQuotationRequest $request
     * @return ApiResource
     * @throws Throwable
     */
    public function store(StoreSalesQuotationRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $salesQuotation = SalesQuotation::create($request->all());
            $salesQuotation
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($salesQuotation);
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
        $salesQuotation = SalesQuotation::from(SalesQuotation::getTableName().' as '.SalesQuotation::$alias)->eloquentFilter($request);

        $salesQuotation = SalesQuotation::joins($salesQuotation, $request->get('join'));

        $salesQuotation = $salesQuotation->where(SalesQuotation::$alias.'.id', $id)->first();

        /*
         * anything except 0 is considered true, including string "false"
         */
        if ($request->get('remaining_info')) {
            $salesReceives = $salesQuotation->salesReceives()->with('items')->get();

            foreach ($salesQuotation->items as $orderItem) {
                $orderItem->quantity_pending = $orderItem->quantity;

                foreach ($salesReceives as $receive) {
                    $receiveItem = $receive->items->firstWhere('sales_order_item_id', $orderItem->id);
                    if ($receiveItem) {
                        $orderItem->quantity_pending -= $receiveItem->quantity;
                    }
                }
            }
        }

        return new ApiResource($salesQuotation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSalesQuotationRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateSalesQuotationRequest $request, $id)
    {
        $salesQuotation = SalesQuotation::findOrFail($id);
        $salesQuotation->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $salesQuotation) {
            $salesQuotation->form->archive();
            $request['number'] = $salesQuotation->form->edited_number;
            $request['old_increment'] = $salesQuotation->form->increment;

            $salesQuotation = SalesQuotation::create($request->all());
            $salesQuotation
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($salesQuotation);
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

        $salesQuotation = SalesQuotation::findOrFail($id);
        $salesQuotation->isAllowedToDelete();
        $salesQuotation->requestCancel($request);

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
