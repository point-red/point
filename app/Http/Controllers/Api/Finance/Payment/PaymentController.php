<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Finance\Payment\Payment;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $payment = Payment::eloquentFilter($request)
            ->joinForm()
            ->notArchived()
            ->select(Payment::getTableName('*'));

        if ($request->has('type')) {
            $paymentType = strtoupper($request->get('type'));
            $payment->where('payment_type', $paymentType);
        }
        if ($request->has('disbursed')) {
            $disbursed = $request->get('disbursed');
            $payment->where('disbursed', $disbursed);
        }

        $payment->with('form');

        $payment = pagination($payment, $request->get('limit'));

        return new ApiCollection($payment);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = \DB::connection('tenant')->transaction(function () use ($request) {
            $payment = Payment::create($request->all());

            $payment
                ->load('form')
                ->load('paymentable')
                ->load('details.referenceable')
                ->load('details.allocation');

            return new ApiResource($payment);
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
        $payment = Payment::eloquentFilter($request)
            ->with('form')
            ->with('paymentable')
            ->with('details.referenceable')
            ->with('details.allocation')
            ->findOrFail($id);

        return new ApiResource($payment);
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
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $payment = Payment::findOrFail($id);
            // TODO update payment detail remaining
            $newPayment = $payment->edit($request->all());

            return new ApiResource($newPayment);
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
        $payment = Payment::findOrFail($id);
        // TODO update payment detail remaining
        $payment->delete();

        return response()->json([], 204);
    }
}
