<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseDownPayment;

use App\Exceptions\IsReferencedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\PurchaseDownPayment\PurchaseDownPayment\StorePurchaseDownPaymentRequest;
use App\Http\Requests\Purchase\PurchaseDownPayment\PurchaseDownPayment\UpdatePurchaseDownPaymentRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $downPayments = PurchaseDownPayment::from(PurchaseDownPayment::getTableName().' as '.PurchaseDownPayment::$alias)->eloquentFilter($request);

        $downPayments = PurchaseDownPayment::joins($downPayments, $request->get('join'));

        $downPayments = pagination($downPayments, $request->get('limit'));

        return new ApiCollection($downPayments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePurchaseDownPaymentRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(StorePurchaseDownPaymentRequest $request)
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
     * @param UpdatePurchaseDownPaymentRequest $request
     * @param  int $id
     * @return void
     */
    public function update(UpdatePurchaseDownPaymentRequest $request, $id)
    {
        $downPayment = PurchaseDownPayment::with('form')->findOrFail($id);
        $downPayment->isAllowedToUpdate();

        $hasPayment = $downPayment->payments()->exists();
        if ($hasPayment && ! $request->get('force')) {
            // Throw error referenced by payment, need parameter force (and maybe need extra permission role)
            throw new IsReferencedException('Cannot delete because referenced by payment.', $downPayment->payments->first());

            return;
        }
        $result = DB::connection('tenant')->transaction(function () use ($request, $downPayment) {
            $payment = $downPayment->payments->first();
            $payment->isAllowedToUpdate();
            $payment->form->archive();

            $downPayment->form->archive();
            $request['number'] = $downPayment->form->edited_number;
            $request['old_increment'] = $downPayment->form->increment;

            $downPayment = PurchaseDownPayment::create($request->all());
            $downPayment->load(['form', 'supplier', 'downpaymentable']);

            return new ApiResource($downPayment);
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
        DB::connection('tenant')->beginTransaction();

        $downPayment = PurchaseDownPayment::findOrFail($id);
        $downPayment->isAllowedToDelete();
        $downPayment->requestCancel($request);

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
