<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseReceived;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Purchase\PurchaseReceived\PurchaseReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReceivedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $purchaseReceiveds = PurchaseReceived::eloquentFilter($request)
            ->join(Form::getTableName(), PurchaseReceived::getTableName() . '.id', '=', Form::getTableName() . '.formable_id')
            ->select(PurchaseReceived::getTableName() . '.*')
            ->where(Form::getTableName() . '.formable_type', PurchaseReceived::class)
            ->with('form')
            ->get();

        return new ApiCollection($purchaseReceiveds);
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
            $purchaseReceived = PurchaseReceived::create($request->all());

            return new ApiResource($purchaseReceived
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
        $purchaseReceived = PurchaseReceived::eloquentFilter($request)
            ->with('form')
            ->with('purchaseOrder')
            ->with('warehouse')
            ->with('supplier')
            ->with('items.allocation')
            ->with('services.allocation')
            ->findOrFail($id);

        return new ApiResource($purchaseReceived);
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
        $purchaseReceived = PurchaseReceived::findOrFail($id);

        $purchaseReceived->delete();

        return response()->json([], 204);
    }
}
