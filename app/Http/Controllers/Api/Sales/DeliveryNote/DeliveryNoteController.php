<?php

namespace App\Http\Controllers\Api\Sales\DeliveryNote;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\DeliveryNote\DeliveryNote\StoreDeliveryNoteRequest;
use App\Http\Requests\Sales\DeliveryNote\DeliveryNote\UpdateDeliveryNoteRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

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
                        ->where(Form::getTableName('formable_type'), DeliveryNote::$morphName);
                });
            }
        }

        $deliveryNotes = pagination($deliveryNotes, $request->get('limit'));

        return new ApiCollection($deliveryNotes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDeliveryNoteRequest $request
     * @return Response
     * @throws Throwable
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
        $deliveryNote = DeliveryNote::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($deliveryNote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDeliveryNoteRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateDeliveryNoteRequest $request, $id)
    {
        $deliveryNote = DeliveryNote::with('form')->findOrFail($id);

        $deliveryNote->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $deliveryNote) {
            $deliveryNote->form->archive();
            $request['number'] = $deliveryNote->form->edited_number;
            $request['old_increment'] = $deliveryNote->form->increment;

            $deliveryNote = DeliveryNote::create($request->all());
            $deliveryNote->load(['form', 'customer', 'items']);

            return new ApiResource($deliveryNote);
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
        $deliveryNote = DeliveryNote::findOrFail($id);
        $deliveryNote->isAllowedToDelete();

        $response = $deliveryNote->requestCancel($request);

        if (! $response) {
            $deliveryNote->deliveryOrder->form->done = false;
            $deliveryNote->deliveryOrder->form->save();
        }

        return response()->json([], 204);
    }
}
