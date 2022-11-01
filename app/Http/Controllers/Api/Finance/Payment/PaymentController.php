<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\Payment\Payment\StorePaymentRequest;
use App\Http\Requests\Finance\Payment\Payment\UpdatePaymentRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use stdClass;
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
        $payment = Payment::from(Payment::getTableName() . ' as ' . Payment::$alias)->eloquentFilter($request);

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
                if (!$paymentDetail->isDownPayment()) {
                    $reference = $paymentDetail->referenceable;
                    $reference->remaining += $paymentDetail->amount;
                    $reference->save();
                    $reference->updateStatus();
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

        if (!$response) {
            foreach ($payment->details as $paymentDetail) {
                if (!$paymentDetail->isDownPayment()) {
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

    public function getReferences(Request $request)
    {
        // Split request filter for each reference type
        $paymentOrderRequest = new Request();
        $downPaymentRequest = new Request();
        $paymentOrderString = 'paymentorder';
        $downPaymentString = 'downpayment';
        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['limit', 'page'])) {
                $paymentOrderRequest->merge([
                    $key => $value
                ]);
                $downPaymentRequest->merge([
                    $key => $value
                ]);
                continue;
            }
            $explodedKey = explode('_', $key);

            switch ($explodedKey[0]) {
                case $paymentOrderString:
                    $keyAttribute = substr($key, strlen($paymentOrderString) + 1); //+1 for _
                    $paymentOrderRequest->merge([
                        $keyAttribute => $value
                    ]);
                    break;

                case $downPaymentString:
                    $keyAttribute = substr($key, strlen($downPaymentString) + 1); //+1 for _
                    $downPaymentRequest->merge([
                        $keyAttribute => $value
                    ]);
                    break;

                default:
                    # code...
                    break;
            }
        }

        $references = new Collection();

        $paymentOrders = PaymentOrder::from(PaymentOrder::getTableName() . ' as ' . PaymentOrder::$alias)->eloquentFilter($paymentOrderRequest);
        $paymentOrders = PaymentOrder::joins($paymentOrders, $paymentOrderRequest->get('join'))->get();
        $paymentOrders->transform(function ($paymentOrder) {
            $transformData = new PaymentOrder();
            $transformData->referenceable_id = $paymentOrder->id;
            $transformData->referenceable_type = $transformData::$morphName;
            $transformData->date = $paymentOrder->form->date;
            $transformData->number = $paymentOrder->form->number;
            $transformData->person = $paymentOrder->paymentable_name;
            $transformData->amount = $paymentOrder->amount;
            $transformData->notes = $paymentOrder->form->notes;
            $transformData->created_by = $paymentOrder->form->createdBy->full_name;
            return $transformData;
        });
        $references = $references->concat($paymentOrders);

        $downPayments = PurchaseDownPayment::from(PurchaseDownPayment::getTableName() . ' as ' . PurchaseDownPayment::$alias)->eloquentFilter($downPaymentRequest);
        $downPayments = PurchaseDownPayment::joins($downPayments, $downPaymentRequest->get('join'))->get();
        $downPayments->transform(function ($downPayment) {
            $transformData = new PurchaseDownPayment();
            $transformData->referenceable_id = $downPayment->id;
            $transformData->referenceable_type = $transformData::$morphName;
            $transformData->date = $downPayment->form->date;
            $transformData->number = $downPayment->form->number;
            $transformData->person = $downPayment->supplier_name;
            $transformData->amount = $downPayment->amount;
            $transformData->notes = $downPayment->form->notes;
            $transformData->created_by = $downPayment->form->createdBy->full_name;
            return $transformData;
        });
        $references = $references->concat($downPayments);

        $references = $references->sortBy('date');
        $paginatedReferences = paginate_collection($references, $request->get('limit'), $request->get('page'));

        return new ApiCollection($paginatedReferences);
    }

    public function getPaymentables(Request $request)
    {
        $paymentables = Payment::from(Payment::getTableName() . ' as ' . Payment::$alias)
            ->select(['paymentable_type', 'paymentable_name'])
            ->eloquentFilter($request);
        $paymentables = pagination($paymentables, $request->get('limit'));

        return new ApiCollection($paymentables);
    }
}
