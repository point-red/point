<?php

namespace App\Http\Controllers\Api\Sales\SalesInvoice;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Customer;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SalesInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index()
    {
        $salesInvoices = SalesInvoice::eloquentFilter($request)
            ->join(Customer::getTableName(), SalesInvoice::getTableName('customer_id'), '=', Customer::getTableName('id'))
            ->select(SalesInvoice::getTableName('*'))
            ->joinForm()
            ->notArchived()
            ->with('form');

        $salesInvoices = pagination($salesInvoices, $request->get('limit'));

        return new ApiCollection($salesInvoices);
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
            $salesInvoice = SalesInvoice::create($request->all());

            $salesInvoice
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($salesInvoice);
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
        $salesInvoice = SalesInvoice::eloquentFilter($request)
            ->with('form')
            ->with('customer')
            ->with('items.item')
            ->with('items.allocation')
            ->with('items.deliveryNote.form')
            ->with('services.service')
            ->with('services.allocation')
            ->findOrFail($id);

        return new ApiResource($salesInvoice);
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
        $salesInvoice = SalesInvoice::findOrFail($id);

        $salesInvoice->delete();

        return response()->json([], 204);
    }
}
