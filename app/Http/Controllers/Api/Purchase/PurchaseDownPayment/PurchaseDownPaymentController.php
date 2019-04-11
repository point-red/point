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
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $downPayments = PurchaseDownPayment::eloquentFilter($request)->with('downpaymentable');

        $downPayments = pagination($downPayments, $request->get('limit'));

        return new ApiCollection($downPayments);
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
            $downPayment = PurchaseDownPayment::create($request->all());
            $downPayment->load('form', 'supplier');

            return new ApiResource($downPayment);
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
        $downPayment = PurchaseDownPayment::eloquentFilter($request)
            ->with('form')
            ->findOrFail($id);

        return new ApiResource($downPayment);
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
