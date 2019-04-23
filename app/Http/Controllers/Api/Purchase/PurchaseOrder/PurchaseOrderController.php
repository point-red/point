<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseOrder;

use App\Model\Form;
use Illuminate\Http\Request;
use App\Model\Master\Supplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Http\Requests\Purchase\PurchaseOrder\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\Purchase\PurchaseOrder\PurchaseOrder\UpdatePurchaseOrderRequest;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('supplier', $fields)) {
                $purchaseOrders = $purchaseOrders->join(Supplier::getTableName(), function ($q) {
                    $q->on(Supplier::getTableName('id'), '=', PurchaseOrder::getTableName('supplier_id'));
                });
            }

            if (in_array('form', $fields)) {
                $purchaseOrders = $purchaseOrders->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', PurchaseOrder::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), PurchaseOrder::class);
                });
            }
        }

        $purchaseOrders = pagination($purchaseOrders, $request->get('limit'));

        return new ApiCollection($purchaseOrders);
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
     * @param StorePurchaseOrderRequest $request
     * @return ApiResource
     * @throws \Throwable
     */
    public function store(StorePurchaseOrderRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $purchaseOrder = PurchaseOrder::create($request->all());
            $purchaseOrder
                ->load('form')
                ->load('supplier')
                ->load('items.item')
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($purchaseOrder);
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
        $purchaseOrder = PurchaseOrder::eloquentFilter($request)->findOrFail($id);

        /*
         * anything except 0 is considered true, including string "false"
         */
        if ($request->get('remaining_info')) {
            $purchaseReceives = $purchaseOrder->purchaseReceives()->with('items')->get();

            foreach ($purchaseOrder->items as $orderItem) {
                $orderItem->quantity_pending = $orderItem->quantity;

                foreach ($purchaseReceives as $receive) {
                    $receiveItem = $receive->items->firstWhere('purchase_order_item_id', $orderItem->id);
                    if ($receiveItem) {
                        $orderItem->quantity_pending -= $receiveItem->quantity;
                    }
                }
            }
        }

        return new ApiResource($purchaseOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePurchaseOrderRequest $request
     * @param int $id
     * @return ApiResource
     * @throws \Throwable
     */
    public function update(UpdatePurchaseOrderRequest $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $purchaseOrder) {
            $purchaseOrder->form->archive();
            $request['number'] = $purchaseOrder->form->edited_number;

            $purchaseOrder = PurchaseOrder::create($request->all());
            $purchaseOrder
                ->load('form')
                ->load('employee')
                ->load('supplier')
                ->load('items.item')
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($purchaseOrder);
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
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->isAllowedToDelete();

        $response = $purchaseOrder->requestCancel($request);

        if (!$response) {
            if ($purchaseOrder->purchaseRequest) {
                $purchaseOrder->purchaseRequest->form->done = false;
                $purchaseOrder->purchaseRequest->form->save();
            }
        }

        return response()->json([], 204);
    }
}
