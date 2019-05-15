<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Finance\Payment\Payment;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Finance\Payment\Payment\StorePaymentRequest;
use App\Http\Requests\Finance\Payment\Payment\UpdatePaymentRequest;

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
                $payment = $payment->leftJoin(Customer::getTableName(), function ($q) {
                    $q->on(Customer::getTableName('id'), '=', Payment::getTableName('paymentable_id'))
                        ->where(Payment::getTableName('paymentable_type'), Customer::$morphName);
                });

                $payment = $payment->leftJoin(Supplier::getTableName(), function ($q) {
                    $q->on(Supplier::getTableName('id'), '=', Payment::getTableName('paymentable_id'))
                        ->where(Payment::getTableName('paymentable_type'), Supplier::$morphName);
                });

                $payment = $payment->leftJoin(Employee::getTableName(), function ($q) {
                    $q->on(Employee::getTableName('id'), '=', Payment::getTableName('paymentable_id'))
                        ->where(Payment::getTableName('paymentable_type'), Employee::$morphName);
                });
            }

            if (in_array('form', $fields)) {
                $payment = $payment->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', Payment::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), Payment::$morphName);
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(StorePaymentRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
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
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Throwable
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
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->isAllowedToDelete();

        $response = $payment->requestCancel($request);

        if (! $response) {
            foreach ($payment->details as $paymentDetail) {
                if (! $paymentDetail->isDownPayment) {
                    $reference = $paymentDetail->referenceable;
                    $reference->remaining += $payment->amount;
                    $reference->form->done = false;
                    $reference->form->save();
                }
            }
        }

        return response()->json([], 204);
    }
}
