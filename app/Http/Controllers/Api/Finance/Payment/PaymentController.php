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
        // TO DO
        // Split request filter for each reference type

        $references = new Collection();

        $request['join'] = 'form;details;account';
        $request['group_by'] = 'payment_order.id';
        $request['fields'] = 'payment_order.*';
        $request['filter_null'] = 'payment_order.payment_id';
        $request['filter_equal'] = [
            'payment_order.payment_type' => 'cash'
        ];
        $request['includes'] = 'form;paymentable;details.account;details.allocation';
        $paymentOrders = PaymentOrder::from(PaymentOrder::getTableName() . ' as ' . PaymentOrder::$alias)->eloquentFilter($request);
        $paymentOrders = PaymentOrder::joins($paymentOrders, $request->get('join'))->get();
        $references = $references->concat($paymentOrders);
        
        $request['join'] = 'form';
        $request['group_by'] = 'purchase_down_payment.id';
        $request['fields'] = 'purchase_down_payment.*';
        unset($request['filter_equal']);
        unset($request['filter_null']);
        $request['includes'] = 'form;supplier';
        $request['filter_date_min'] = [
            'form.date' => date('Y-m-01')
        ];
        $request['filter_date_max'] = [
            'form.date' => date('Y-m-t')
        ];
        $downPayments = PurchaseDownPayment::from(PurchaseDownPayment::getTableName() . ' as ' . PurchaseDownPayment::$alias)->eloquentFilter($request);
        $downPayments = PurchaseDownPayment::joins($downPayments, $request->get('join'))->get();
        $references = $references->concat($downPayments);

        $paginatedReferences = $this->paginate($references, $request->get('limit'));

        return new ApiCollection($paginatedReferences);
    }

    public function getPaymentables(Request $request)
    {
        $paymentables = Payment::groupBy('paymentable_type')->groupBy('paymentable_name')->select(['paymentable_type', 'paymentable_name']);
        $paymentables = pagination($paymentables, $request->get('limit'));

        return new ApiCollection($paymentables);
    }

    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        // TO DO, make this function reusable
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
