<?php

namespace App\Http\Controllers\Api\Sales\SalesInvoice;

use App\Model\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Sales\SalesInvoice\SalesInvoice;

class SalesInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $salesInvoices = SalesInvoice::eloquentFilter($request);

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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $salesInvoice = SalesInvoice::eloquentFilter($request)
            ->with('form', 'items')
            ->findOrFail($id);

        return new ApiResource($salesInvoice);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int  $id
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        // TODO prevent delete if referenced by payment
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $salesInvoice = SalesInvoice::findOrFail($id);

            $newSalesInvoice = $salesInvoice->edit($request->all());

            $salesInvoice->detachDownPayments();

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
        $salesInvoice = SalesInvoice::findOrFail($id);
        $salesInvoice->isAllowedToDelete();

        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $salesInvoice->detachDownPayments();
            $response = $salesInvoice->requestCancel($request);

            if (!$response) {
                foreach ($salesInvoice->items as $salesInvoiceItem) {
                    if ($salesInvoiceItem->deliveryNote) {
                        $salesInvoiceItem->deliveryNote->form->done = false;
                        $salesInvoiceItem->deliveryNote->form->save();
                    }
                }
            }

            return response()->json([], 204);
        });

        return $result;
    }
}
