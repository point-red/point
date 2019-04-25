<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Model\Form;
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
        $payment = Payment::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('paymentable', $fields)) {
                $model = $payment->paymentable_type;
                $payment = $payment->join($model::getTableName(), function ($q) use ($model) {
                    $q->on($model::getTableName('id'), '=', Payment::getTableName('paymentable_id'));
                });
            }

            if (in_array('form', $fields)) {
                $payment = $payment->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', Payment::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), Payment::class);
                });
            }
        }

        if ($request->has('type')) {
            $paymentType = strtoupper($request->get('type'));
            $payment->where('payment_type', $paymentType);
        }

        if ($request->has('disbursed')) {
            $disbursed = $request->get('disbursed');
            $payment->where('disbursed', $disbursed);
        }

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
        $payment = Payment::eloquentFilter($request)->findOrFail($id);

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

            $payment->form->archive();

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
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
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
