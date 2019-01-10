<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $deliverOrders = DeliveryOrder::eloquentFilter($request)
            ->join(Form::getTableName(), DeliveryOrder::getTableName().'.id', '=', Form::getTableName().'.formable_id')
            ->join(Customer::getTableName(), DeliveryOrder::getTableName().'.supplier_id', '=', Supplier::getTableName().'.id')
            ->select(DeliveryOrder::getTableName().'.*')
            ->where(Form::getTableName().'.formable_type', DeliveryOrder::class)
            ->with('form');

        $deliverOrders = pagination($deliverOrders, $request->get('limit'));

        return new ApiCollection($deliverOrders);
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
            $deliveryOrder = DeliveryOrder::create($request->all());

            return new ApiResource($deliveryOrder
                ->load('form')
                ->load('customer')
                ->load('items.allocation')
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
        $deliveryOrder = DeliveryOrder::eloquentFilter($request)
            ->with('form')
            ->with('salesOrder.form')
            ->with('warehouse')
            ->with('customer')
            ->with('items.item')
            ->with('items.allocation')
            ->findOrFail($id);

        return new ApiResource($deliveryOrder);
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
        $deliveryOrder = DeliveryOrder::findOrFail($id);

        $deliveryOrder->delete();

        return response()->json([], 204);
    }
}
