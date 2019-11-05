<?php

namespace App\Http\Controllers\Api\Sales\SalesContract;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesContract\SalesContract\StoreSalesContractRequest;
use App\Http\Requests\Sales\SalesContract\SalesContract\UpdateSalesContractRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesContract\SalesContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $salesContracts = SalesContract::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('customer', $fields)) {
                $salesContracts->join(Customer::getTableName(), function ($q) {
                    $q->on(Customer::getTableName('id'), '=', SalesContract::getTableName('customer_id'));
                });
            }

            if (in_array('form', $fields)) {
                $salesContracts->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', SalesContract::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), SalesContract::$morphName);
                });
            }
        }

        $salesContracts = pagination($salesContracts, $request->get('limit'));

        return new ApiCollection($salesContracts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreSalesContractRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(StoreSalesContractRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $salesContract = SalesContract::create($request->all());
            $salesContract
                ->load('form')
                ->load('customer');

            return new ApiResource($salesContract);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  Request $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $salesContract = SalesContract::eloquentFilter($request)->findOrFail($id);

        if ($request->get('remaining_info')) {
            $salesOrders = $salesContract->salesOrders()->with('items')->get();

            foreach ($salesContract->items as $contractItem) {
                $contractItem->quantity_pending = $contractItem->quantity;

                foreach ($salesOrders as $order) {
                    $orderItem = $order->items->firstWhere('sales_contract_item_id', $contractItem->id);
                    if ($orderItem) {
                        $contractItem->quantity_pending -= $orderItem->quantity;
                    }
                }
            }

            foreach ($salesContract->groupItems as $contractGroupItem) {
                $contractGroupItem->quantity_pending = $contractGroupItem->quantity;

                foreach ($salesOrders as $order) {
                    $orderItem = $order->items->firstWhere('sales_contract_group_item_id', $contractGroupItem->id);
                    if ($orderItem) {
                        $contractGroupItem->quantity_pending -= $orderItem->quantity;
                    }
                }
            }
        }

        return new ApiResource($salesContract);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSalesContractRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function update(UpdateSalesContractRequest $request, $id)
    {
        $salesContract = SalesContract::with('form')->findOrFail($id);
        $salesContract->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $salesContract) {
            $salesContract->form->archive();
            $request['number'] = $salesContract->form->edited_number;
            $request['old_increment'] = $salesContract->form->increment;

            $salesContract = SalesContract::create($request->all());
            $salesContract->load('form');

            return new ApiResource($salesContract);
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
        $salesContract = SalesContract::findOrFail($id);
        $salesContract->isAllowedToDelete();

        $salesContract->requestCancel($request);

        return response()->json([], 204);
    }
}
