<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseDownPayment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;

class PurchaseDownPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index()
    {
        $purchaseDownPayments = PurchaseDownPayment::eloquentFilter($request)
            ->select(PurchaseDownPayment::getTableName('*'))
            ->joinForm()
            ->notArchived()
            ->with('form');

        $purchaseDownPayments = pagination($purchaseDownPayments, $request->get('limit'));

        return new ApiCollection($purchaseDownPayments);
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
            $purchaseDownPayment = PurchaseDownPayment::create($request->all());
            $purchaseDownPayment
                ->load('form')
                ->load('employee')
                ->load('supplier')
                ->load('items.item')
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($purchaseDownPayment);
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
        $purchaseDownPayment = PurchaseDownPayment::eloquentFilter($request)
            ->with('form')
            ->findOrFail($id);

        return new ApiResource($purchaseDownPayment);
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
