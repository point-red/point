<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseReceive;

use Illuminate\Http\Request;
use App\Model\Master\Supplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;

class PurchaseReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $purchaseReceives = PurchaseReceive::eloquentFilter($request)
            ->join(Supplier::getTableName(), PurchaseReceive::getTableName('supplier_id'), '=', Supplier::getTableName('id'))
            ->joinForm()
            ->notArchived()
            ->with('form');

        $purchaseReceives = pagination($purchaseReceives, $request->get('limit'));

        return new ApiCollection($purchaseReceives);
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
            $purchaseReceive = PurchaseReceive::create($request->all());
            $purchaseReceive
                ->load('form')
                ->load('supplier')
                ->load('items.item')
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($purchaseReceive);
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
        $purchaseReceive = PurchaseReceive::eloquentFilter($request)
            ->with('form')
            ->with('purchaseOrder.form')
            ->with('warehouse')
            ->with('supplier')
            ->with('items.item')
            ->with('items.allocation')
            ->with('services.service')
            ->with('services.allocation')
            ->findOrFail($id);

        return new ApiResource($purchaseReceive);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return ApiResource
     * @throws \Throwable
     */
    public function update(Request $request, $id)
    {
        // TODO prevent delete if referenced by purchase invoice
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $purchaseReceive = PurchaseReceive::findOrFail($id);

            $newPurchaseReceive = $purchaseReceive->edit($request->all());

            return new ApiResource($newPurchaseReceive);
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
        $purchaseReceive = PurchaseReceive::findOrFail($id);

        $purchaseReceive->delete();

        return response()->json([], 204);
    }
}
