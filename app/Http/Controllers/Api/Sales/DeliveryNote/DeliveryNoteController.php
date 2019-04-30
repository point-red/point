<?php

namespace App\Http\Controllers\Api\Sales\DeliveryNote;

use Illuminate\Http\Request;
use App\Model\Master\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Http\Requests\Sales\DeliveryNote\DeliveryNote\StoreDeliveryNoteRequest;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $deliveryNotes = DeliveryNote::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('customer', $fields)) {
                $deliveryNotes->join(Customer::getTableName(), function ($q) {
                    $q->on(Customer::getTableName('id'), '=', DeliveryNote::getTableName('customer_id'));
                });
            }

            if (in_array('form', $fields)) {
                $deliveryNotes->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', DeliveryNote::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), DeliveryNote::class);
                });
            }
        }

        $deliveryNotes = pagination($deliveryNotes, $request->get('limit'));

        return new ApiCollection($deliveryNotes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(StoreDeliveryNoteRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $deliveryNote = DeliveryNote::create($request->all());
            $deliveryNote
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($deliveryNote);
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
        $deliveryNote = DeliveryNote::eloquentFilter($request)
            ->with('form')
            ->with('deliveryOrder.form')
            ->with('warehouse')
            ->with('customer')
            ->with('items.item')
            ->with('items.allocation')
            ->findOrFail($id);

        return new ApiResource($deliveryNote);
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
        // TODO prevent delete if referenced by sales invoice
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $salesInvoice = SalesInvoice::findOrFail($id);

            $newSalesInvoice = $salesInvoice->edit($request->all());

            return new ApiResource($newSalesInvoice);
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
        $deliveryNote = DeliveryNote::findOrFail($id);
        $deliveryNote->isAllowedToDelete();

        $response = $deliveryNote->requestCancel($request);

        if (!$response) {
            $deliveryNote->deliveryOrder->form->done = false;
            $deliveryNote->deliveryOrder->form->save();
        }

        return response()->json([], 204);
    }
}
