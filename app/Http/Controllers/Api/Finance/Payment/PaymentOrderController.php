<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Model\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Finance\Payment\PaymentOrder;

class PaymentOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $payment = PaymentOrder::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('paymentable', $fields)) {
                $model = $payment->paymentable_type;
                $payment = $payment->join($model::getTableName(), function ($q) use ($model) {
                    $q->on($model::getTableName('id'), '=', PaymentOrder::getTableName('paymentable_id'));
                });
            }

            if (in_array('form', $fields)) {
                $payment = $payment->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', PaymentOrder::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), PaymentOrder::class);
                });
            }
        }

        if ($request->has('type')) {
            $paymentType = strtoupper($request->get('type'));
            $payment->where('payment_type', $paymentType);
        }

        $payment = pagination($payment, $request->get('limit'));

        return new ApiCollection($payment);
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
            $payment = PaymentOrder::create($request->all());

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
        $payment = PaymentOrder::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($payment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function update(Request $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $payment = PaymentOrder::findOrFail($id);

            $payment->form->archive();

            $payment = PaymentOrder::create($request->all());

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
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $payment = PaymentOrder::findOrFail($id);
        $payment->isAllowedToDelete();

        $response = $payment->requestCancel($request);

        if (!$response) {
            foreach ($payment->details as $paymentDetail) {
                if ($paymentDetail->referenceable) {
                    $paymentDetail->referenceable->form->done = false;
                    $paymentDetail->referenceable->form->save();
                }
            }
        }

        return response()->json([], 204);
    }
}
