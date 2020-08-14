<?php

namespace App\Http\Controllers\Api\Sales\SalesDownPayment;

use App\Exceptions\IsReferencedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesDownPayment\StoreSalesDownPaymentRequest;
use App\Http\Requests\Sales\SalesDownPayment\UpdateSalesDownPaymentRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesDownPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $downPayments = SalesDownPayment::from(SalesDownPayment::getTableName().' as '.SalesDownPayment::$alias)->eloquentFilter($request);

        $downPayments = SalesDownPayment::joins($downPayments, $request->get('join'));

        $downPayments = pagination($downPayments, $request->get('limit'));

        return new ApiCollection($downPayments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSalesDownPaymentRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(StoreSalesDownPaymentRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $downPayment = SalesDownPayment::create($request->all());
            $downPayment->load('form', 'customer');

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
        $downPayment = SalesDownPayment::eloquentFilter($request)
            ->with('form')
            ->findOrFail($id);

        return new ApiResource($downPayment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSalesDownPaymentRequest $request
     * @param  int $id
     * @return void
     */
    public function update(UpdateSalesDownPaymentRequest $request, $id)
    {
        $downPayment = SalesDownPayment::with('form')->findOrFail($id);
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

            $downPayment = SalesDownPayment::create($request->all());
            $downPayment->load(['form', 'customer', 'downpaymentable']);

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

        $downPayment = SalesDownPayment::findOrFail($id);
        $downPayment->isAllowedToDelete();
        $downPayment->requestCancel($request);

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
