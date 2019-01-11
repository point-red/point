<?php

namespace App\Http\Controllers\Api\Sales\DeliveryNote;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index()
    {
        $deliveryNote = DeliveryNote::eloquentFilter($request)
            ->join(Form::getTableName(), DeliveryNote::getTableName().'.id', '=', Form::getTableName().'.formable_id')
            ->join(Customer::getTableName(), DeliveryNote::getTableName().'.customer_id', '=', Customer::getTableName().'.id')
            ->select(DeliveryNote::getTableName().'.*')
            ->where(Form::getTableName().'.formable_type', DeliveryNote::class)
            ->with('form');

        $deliveryNote = pagination($deliveryNote, $request->get('limit'));

        return new ApiCollection($deliveryNote);
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
            $deliveryNote = DeliveryNote::create($request->all());

            return new ApiResource($deliveryNote
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
        $deliveryNote = DeliveryNote::findOrFail($id);

        $deliveryNote->delete();

        return response()->json([], 204);
    }
}
