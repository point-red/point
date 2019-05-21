<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseReceive;

use App\Model\Form;
use Illuminate\Http\Request;
use App\Model\Master\Supplier;
use App\Model\Inventory\Inventory;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Http\Requests\Purchase\PurchaseReceive\PurchaseReceive\StorePurchaseReceiveRequest;

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
        $purchaseReceives = PurchaseReceive::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('supplier', $fields)) {
                $purchaseReceives = $purchaseReceives->join(Supplier::getTableName(), function ($q) {
                    $q->on(Supplier::getTableName('id'), '=', PurchaseReceive::getTableName('supplier_id'));
                });
            }

            if (in_array('form', $fields)) {
                $purchaseReceives = $purchaseReceives->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', PurchaseReceive::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), PurchaseReceive::$morphName);
                });
            }
        }

        $purchaseReceives = pagination($purchaseReceives, $request->get('limit'));

        return new ApiCollection($purchaseReceives);
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
     *      - allocation_id (Int, Optional)
     *  - services (Array) :
     *      - service_id (Int)
     *      - quantity (Decimal)
     *      - price (Decimal)
     *      - discount_percent (Decimal, Optional)
     *      - discount_value (Decimal, Optional)
     *      - taxable (Boolean, Optional)
     *      - description (String)
     *      - allocation_id (Int, Optional).
     *
     * @param StorePurchaseReceiveRequest $request
     * @return ApiResource
     * @throws \Throwable
     */
    public function store(StorePurchaseReceiveRequest $request)
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
        $purchaseReceive = PurchaseReceive::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($purchaseReceive);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return ApiResource
     */
    public function edit(Request $request, $id)
    {
        $purchaseReceive = PurchaseReceive::eloquentFilter($request)->findOrFail($id)->load('items');

        $orderItems = optional($purchaseReceive->purchaseOrder)->items;

        foreach ($orderItems as $orderItem) {
            $orderItem->quantity_pending = $orderItem->quantity;
            $orderItem->quantity = 0;
            foreach ($purchaseReceive->purchaseOrder->purchaseReceives as $receive) {
                $receiveItem = $receive->items->firstWhere('purchase_order_item_id', $orderItem->id);
                if (! $receiveItem) {
                    continue;
                }

                if ($receiveItem->purchase_receive_id != $id) {
                    $orderItem->quantity_pending -= $receiveItem->quantity;
                } else {
                    $orderItem->quantity = $receiveItem->quantity;
                }
            }
        }

        $purchaseReceive->order_items = $orderItems;

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
        $purchaseReceive = PurchaseReceive::findOrFail($id);
        $purchaseReceive->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $purchaseReceive) {
            $purchaseReceive->form->archive();

            Inventory::where('form_id', $purchaseReceive->form->id)->delete();

            $request['number'] = $purchaseReceive->form->edited_number;

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
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $purchaseReceive = PurchaseReceive::findOrFail($id);
        $purchaseReceive->isAllowedToDelete();

        $response = $purchaseReceive->requestCancel($request);

        if (! $response) {
            $purchaseReceive->purchaseOrder->form->done = false;
            $purchaseReceive->purchaseOrder->form->save();
        }

        return response()->json([], 204);
    }
}
