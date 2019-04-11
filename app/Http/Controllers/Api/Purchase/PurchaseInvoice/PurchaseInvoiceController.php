<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseInvoice;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Http\Requests\Purchase\PurchaseInvoice\PurchaseInvoice\StorePurchaseInvoiceRequest;
use App\Http\Requests\Purchase\PurchaseInvoice\PurchaseInvoice\UpdatePurchaseInvoiceRequest;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;

class PurchaseInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $purchaseInvoices = PurchaseInvoice::eloquentFilter($request);

        $purchaseInvoices = pagination($purchaseInvoices, $request->get('limit'));

        return new ApiCollection($purchaseInvoices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Throwable
     * @return ApiResource
     */
    public function store(StorePurchaseInvoiceRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $purchaseInvoice = PurchaseInvoice::create($request->all());

            $purchaseInvoice
                ->load('form')
                ->load('supplier')
                ->load('items.item')
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($purchaseInvoice);
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
        $purchaseInvoice = PurchaseInvoice::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($purchaseInvoice);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int  $id
     * @return ApiResource
     */
    public function update(UpdatePurchaseInvoiceRequest $request, $id)
    {
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        $purchaseInvoice->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $purchaseInvoice) {
            $purchaseInvoice->form->archive();
            $request['number'] = $purchaseInvoice->form->edited_number;

            $purchaseInvoice = PurchaseInvoice::create($request->all());
            $purchaseInvoice
            ->load('form')
            ->load('supplier')
            ->load('items.item')
            ->load('items.allocation')
            ->load('services.service')
            ->load('services.allocation');

            return new ApiResource($purchaseInvoice);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        $purchaseInvoice->isAllowedToUpdate();

        return $purchaseInvoice->requestCancel();
    }
}
