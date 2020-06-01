<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseInvoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\PurchaseInvoice\PurchaseInvoice\StorePurchaseInvoiceRequest;
use App\Http\Requests\Purchase\PurchaseInvoice\PurchaseInvoice\UpdatePurchaseInvoiceRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

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
        $purchaseInvoices = PurchaseInvoice::from(PurchaseInvoice::getTableName().' as '.PurchaseInvoice::$alias)->eloquentFilter($request);

        $purchaseInvoices = PurchaseInvoice::joins($purchaseInvoices, $request->get('join'));

        $purchaseInvoices = pagination($purchaseInvoices, $request->get('limit'));

        return new ApiCollection($purchaseInvoices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePurchaseInvoiceRequest $request
     * @return ApiResource
     * @throws Throwable
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
     * @param UpdatePurchaseInvoiceRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdatePurchaseInvoiceRequest $request, $id)
    {
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        $purchaseInvoice->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $purchaseInvoice) {
            $purchaseInvoice->form->archive();
            $request['number'] = $purchaseInvoice->form->edited_number;
            $request['old_increment'] = $purchaseInvoice->form->increment;

            $purchaseInvoice = PurchaseInvoice::create($request->all());
            $purchaseInvoice->load([
                'form',
                'supplier',
                'items.item',
                'items.allocation',
                'services.service',
                'services.allocation',
            ]);

            return new ApiResource($purchaseInvoice);
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
        DB::connection('tenant')->beginTransaction();

        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        $purchaseInvoice->isAllowedToDelete();
        $purchaseInvoice->requestCancel($request);

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
