<?php

namespace App\Http\Controllers\Api\Purchase\PurchasePaymentOrder;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchasePaymentOrder\PurchasePaymentOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PurchasePaymentOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index()
    {
        $paymentOrders = PurchasePaymentOrder::eloquentFilter($request)
            ->joinForm()
            ->join(Supplier::getTableName(), PurchasePaymentOrder::getTableName('supplier_id'), '=', Supplier::getTableName('id'))
            ->notArchived()
            ->select(PurchasePaymentOrder::getTableName('*'))
            ->with('form');

        $paymentOrders = pagination($paymentOrders, $request->get('limit'));

        return new ApiCollection($paymentOrders);
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
            $paymentOrder = PurchasePaymentOrder::create($request->all());
            $paymentOrder
                ->load('form')
                ->load('supplier')
                ->load('cutOffs')
                ->load('downPayments')
                ->load('invoices')
                ->load('others')
                ->load('returns');

            return new ApiResource($paymentOrder);
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
        $paymentOrder = PurchasePaymentOrder::eloquentFilter($request)
            ->load('form')
            ->load('supplier')
            ->load('cutOffs')
            ->load('downPayments')
            ->load('invoices')
            ->load('others')
            ->load('returns');
            ->findOrFail($id);

        // Todo: inject remaining amount

        return new ApiResource($paymentOrder);
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
        //
    }
}
