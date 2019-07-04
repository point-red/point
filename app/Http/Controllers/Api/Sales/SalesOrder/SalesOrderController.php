<?php

namespace App\Http\Controllers\Api\Sales\SalesOrder;

use App\Model\Form;
use Illuminate\Http\Request;
use App\Model\Master\Customer;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Http\Requests\Sales\SalesOrder\SalesOrder\StoreSalesOrderRequest;
use App\Http\Requests\Sales\SalesOrder\SalesOrder\UpdateSalesOrderRequest;
use Throwable;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $salesOrders = SalesOrder::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('customer', $fields)) {
                $salesOrders->join(Customer::getTableName(), function ($q) {
                    $q->on(Customer::getTableName('id'), '=', SalesOrder::getTableName('customer_id'));
                });
            }

            if (in_array('form', $fields)) {
                $salesOrders->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', SalesOrder::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), SalesOrder::$morphName);
                });
            }
        }

        $salesOrders = pagination($salesOrders, $request->get('limit'));

        return new ApiCollection($salesOrders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSalesOrderRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(StoreSalesOrderRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $salesOrder = SalesOrder::create($request->all());
            $salesOrder
                ->load('form')
                ->load('customer')
                ->load('items.allocation')
                ->load('services.allocation');

            return new ApiResource($salesOrder);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $salesOrder = SalesOrder::eloquentFilter($request)->findOrFail($id);

        if ($request->get('remaining_info')) {
            $deliveryOrders = $salesOrder->deliveryOrders()->with('items')->get();

            foreach ($salesOrder->items as $salesOrderItem) {
                $salesOrderItem->quantity_pending = $salesOrderItem->quantity;

                foreach ($deliveryOrders as $deliveryOrder) {
                    $deliveryOrderItem = $deliveryOrder->items->firstWhere('sales_order_item_id', $salesOrderItem->id);
                    if ($deliveryOrderItem) {
                        $salesOrderItem->quantity_pending -= $deliveryOrderItem->quantity;
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
        // TODO prevent delete if referenced by delivery order
        $salesOrder = SalesOrder::with('form')->findOrFail($id);

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
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($salesOrder);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $salesOrder = SalesOrder::findOrFail($id);
        $salesOrder->isAllowedToDelete();

        $response = $salesOrder->requestCancel($request);

        if (! $response) {
            if ($salesOrder->salesQuotation) {
                $salesOrder->salesQuotation->form->done = false;
                $salesOrder->salesQuotation->form->save();
            }
        }

        return response()->json([], 204);
    }
}
