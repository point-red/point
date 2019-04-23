<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseDownPayment;

use App\Http\Requests\Purchase\PurchaseDownPayment\PurchaseDownPayment\StorePurchaseDownPaymentRequest;
use App\Http\Requests\Purchase\PurchaseDownPayment\PurchaseDownPayment\UpdatePurchaseDownPaymentRequest;
use App\Model\Form;
use App\Model\Master\Supplier;
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

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('supplier', $fields)) {
                $downPayments = $downPayments->join(Supplier::getTableName(), function ($q) {
                    $q->on(Supplier::getTableName('id'), '=', PurchaseDownPayment::getTableName('supplier_id'));
                });
            }

            if (in_array('form', $fields)) {
                $downPayments = $downPayments->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', PurchaseDownPayment::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), PurchaseDownPayment::class);
                });
            }
        }

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
        // currently doesn't have update method
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
        $downPayment = PurchaseDownPayment::findOrFail($id);
        $downPayment->isAllowedToDelete();

        $downPayment->requestCancel($request);

        return response()->json([], 204);
    }
}
