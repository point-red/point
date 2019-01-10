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

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index()
    {
        $salesOrders = SalesOrder::eloquentFilter($request)
            ->join(Form::getTableName(), SalesOrder::getTableName().'.id', '=', Form::getTableName().'.formable_id')
            ->join(Customer::getTableName(), SalesOrder::getTableName().'.customer_id', '=', Customer::getTableName().'.id')
            ->select(SalesOrder::getTableName().'.*')
            ->where(Form::getTableName().'.formable_type', SalesOrder::class)
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
    public function show($id)
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
