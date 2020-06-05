<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\Payment\Payment\StorePaymentRequest;
use App\Http\Requests\Finance\Payment\Payment\UpdatePaymentRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Finance\Payment\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

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
        $payment = Payment::from(Payment::getTableName().' as '.Payment::$alias)->eloquentFilter($request);

        $payment = Payment::joins($payment, $request->get('join'));

        $payment = pagination($payment, $request->get('limit'));

        return new ApiCollection($payment);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePaymentRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(StorePaymentRequest $request)
    {
        return DB::connection('tenant')->transaction(function () use ($request) {
            $payment = Payment::create($request->all());

            $payment
                ->load('form')
                ->load('paymentable')
                ->load('details.allocation')
                ->load('details.referenceable.form');

            return new ApiResource($payment);
        });
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
        $payment = Payment::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($payment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return Response
     * @throws Throwable
     */
    public function update(UpdatePaymentRequest $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $payment = Payment::findOrFail($id);

            $payment->form->archive();

            foreach ($payment->details as $paymentDetail) {
                if (! $paymentDetail->isDownPayment()) {
                    $reference = $paymentDetail->referenceable;
                    $reference->remaining += $paymentDetail->amount;
                    $reference->save();
                    $reference->updateIfDone();
                }
            }

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
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->isAllowedToDelete();

        $response = $payment->requestCancel($request);

        if (! $response) {
            foreach ($payment->details as $paymentDetail) {
                if (! $paymentDetail->isDownPayment()) {
                    $reference = $paymentDetail->referenceable;
                    $reference->remaining += $payment->amount;
                    $reference->save();
                    $reference->form->done = false;
                    $reference->form->save();
                }
            }
        }

        return response()->json([], 204);
    }
}
