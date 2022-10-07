<?php

namespace App\Http\Controllers\Api\Sales\SalesInvoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesInvoice\SalesInvoice\StoreSalesInvoiceRequest;
use App\Http\Requests\Sales\SalesInvoice\SalesInvoice\UpdateSalesInvoiceRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class SalesInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $salesInvoices = SalesInvoice::from(SalesInvoice::getTableName().' as '.SalesInvoice::$alias)->eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('customer', $fields)) {
                $salesInvoices = $salesInvoices->join(Customer::getTableName().' as '.Customer::$alias, function ($q) {
                    $q->on(SalesInvoice::$alias.'.customer_id', '=', Customer::$alias.'.id');
                });
            }
    
            if (in_array('form', $fields)) {
                $salesInvoices = $salesInvoices->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                    $q->on(Form::$alias.'.formable_id', '=', SalesInvoice::$alias.'.id')
                        ->where(Form::$alias.'.formable_type', SalesInvoice::$morphName);
                });
            }
        }

        $salesInvoices = pagination($salesInvoices, $request->get('limit'));

        return new ApiCollection($salesInvoices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSalesInvoiceRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(StoreSalesInvoiceRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $salesInvoice = SalesInvoice::create($request->all());

            $salesInvoice
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($salesInvoice);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $salesInvoice = SalesInvoice::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($salesInvoice);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSalesInvoiceRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateSalesInvoiceRequest $request, $id)
    {
        // TODO prevent delete if referenced by payment
        $salesInvoice = SalesInvoice::findOrFail($id);
        $salesInvoice->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $salesInvoice) {
            $salesInvoice->detachDownPayments();

            $salesInvoice->form->archive();
            $request['number'] = $salesInvoice->form->edited_number;
            $request['old_increment'] = $salesInvoice->form->increment;

            $salesInvoice = SalesInvoice::create($request->all());
            $salesInvoice
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($salesInvoice);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     * @throws Throwable
     */
    public function destroy(Request $request, $id)
    {
        $salesInvoice = SalesInvoice::findOrFail($id);
        $salesInvoice->isAllowedToDelete();

        $result = DB::connection('tenant')->transaction(function () use ($request, $salesInvoice) {
            $salesInvoice->detachDownPayments();
            $response = $salesInvoice->requestCancel($request);

            if (! $response) {
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
