<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseRequest;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        return new ApiCollection(PurchaseRequest::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Request :
     *  - required_date (Date)
     *  - number (String)
     *  - employee_id (Int)
     *  - supplier_id (Int, Optional)
     *  - items (Array) :
     *      - item_id (Int)
     *      - quantity (Decimal)
     *      - unit (String)
     *      - converter (Decimal)
     *      - price (Decimal)
     *      - description (String Optional)
     *      - allocation_id (Int Optional)
     *  - services (Array) :
     *      - service_id (Int)
     *      - quantity (Decimal)
     *      - price (Decimal)
     *      - description (String Optional)
     *      - allocation_id (Int Optional)
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Throwable
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
        ]);

        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $purchaseRequest = PurchaseRequest::create($request->all());

            return new ApiResource($purchaseRequest);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $purchaseRequest = PurchaseRequest::with('form', 'items.allocation', 'services.allocation', 'employee', 'supplier')
            ->findOrFail($id);

        return new ApiResource($purchaseRequest);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
