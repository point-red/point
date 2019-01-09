<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseInvoice;

use App\Model\Form;
use Illuminate\Http\Request;
use App\Model\Master\Supplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;

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
        $purchaseInvoices = PurchaseInvoice::eloquentFilter($request)
            ->join(Form::getTableName(), PurchaseInvoice::getTableName().'.id', '=', Form::getTableName().'.formable_id')
            ->join(Supplier::getTableName(), PurchaseInvoice::getTableName().'.supplier_id', '=', Supplier::getTableName().'.id')
            ->select(PurchaseInvoice::getTableName().'.*')
            ->where(Form::getTableName().'.formable_type', PurchaseInvoice::class)
            ->with('form')
            ->get();

        return new ApiCollection($purchaseInvoices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Throwable
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $purchaseInvoice = PurchaseInvoice::create($request->all());

            return new ApiResource($purchaseInvoice
                ->load('form')
                ->load('supplier')
                ->load('items.allocation')
                ->load('services.allocation')
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
        $purchaseInvoice = PurchaseInvoice::eloquentFilter($request)
            ->with('form')
            ->with('warehouse')
            ->with('supplier')
            ->with('items.item')
            ->with('items.allocation')
            ->with('services.service')
            ->with('services.allocation')
            ->findOrFail($id);

        return new ApiResource($purchaseInvoice);
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
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);

        $purchaseInvoice->delete();

        return response()->json([], 204);
    }
}
