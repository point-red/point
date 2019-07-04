<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use Throwable;
use App\Model\Form;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Model\Master\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Http\Requests\Sales\DeliveryOrder\DeliveryOrder\UpdateDeliveryOrderRequest;

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
        $deliverOrders = DeliveryOrder::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('customer', $fields)) {
                $deliverOrders->join(Customer::getTableName(), function ($q) {
                    $q->on(Customer::getTableName('id'), '=', DeliveryOrder::getTableName('customer_id'));
                });
            }

            if (in_array('form', $fields)) {
                $deliverOrders->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', DeliveryOrder::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), DeliveryOrder::$morphName);
                });
            }
        }

        $deliverOrders = pagination($deliverOrders, $request->get('limit'));

        return new ApiCollection($deliverOrders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $deliveryOrder = DeliveryOrder::create($request->all());
            $deliveryOrder
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($deliveryOrder);
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
        $deliveryOrder = DeliveryOrder::eloquentFilter($request)->findOrFail($id);

        if ($request->get('remaining_info')) {
            $deliveryNotes = $deliveryOrder->deliveryNotes()->with('items')->get();

            foreach ($deliveryOrder->items as $deliveryOrderItem) {
                $deliveryOrderItem->quantity_pending = $deliveryOrderItem->quantity;

                foreach ($deliveryNotes as $deliveryNote) {
                    $deliveryNoteItem = $deliveryNote->items->firstWhere('delivery_order_item_id', $deliveryOrderItem->id);
                    if ($deliveryNoteItem) {
                        $deliveryOrderItem->quantity_pending -= $deliveryNoteItem->quantity;
                    }
                }
            }
        }

        return new ApiResource($deliveryOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDeliveryOrderRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateDeliveryOrderRequest $request, $id)
    {
        // TODO prevent delete if referenced by delivery order
        $deliveryOrder = DeliveryOrder::with('form')->findOrFail($id);

        $deliveryOrder->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $deliveryOrder) {
            $deliveryOrder->form->archive();
            $request['number'] = $deliveryOrder->form->edited_number;
            $request['old_increment'] = $deliveryOrder->form->increment;

            $deliveryOrder = DeliveryOrder::create($request->all());
            $deliveryOrder->load(['form', 'customer', 'items']);

            return new ApiResource($deliveryOrder);
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
        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $deliveryOrder->isAllowedToDelete();

        $response = $deliveryOrder->requestCancel($request);

        if (! $response) {
            if ($deliveryOrder->salesOrder) {
                $deliveryOrder->salesOrder->form->done = false;
                $deliveryOrder->salesOrder->form->save();
            }
        }

        return response()->json([], 204);
    }
}
