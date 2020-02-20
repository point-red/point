<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\CutOff\StoreSalesDownPaymentRequest;
use App\Http\Requests\Accounting\CutOff\UpdateSalesDownPaymentRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffSalesDownPayment;
use App\Model\Master\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CutOffSalesDownPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $cutOffSalesDownPayments = CutOffSalesDownPayment::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('customer', $fields)) {
                $cutOffSalesDownPayments = $cutOffSalesDownPayments->join(Customer::getTableName(), function ($q) {
                    $q->on(Customer::getTableName('id'), '=', CutOffSalesDownPayment::getTableName('customer_id'));
                });
            }

            if (in_array('chartOfAccount', $fields)) {
                $cutOffSalesDownPayments = $cutOffSalesDownPayments->join(ChartOfAccount::getTableName(), function ($q) {
                    $q->on(ChartOfAccount::getTableName('id'), '=', CutOffSalesDownPayment::getTableName('chart_of_account_id'));
                });
            }

            if (in_array('cutOff', $fields)) {
                $cutOffSalesDownPayments = $cutOffSalesDownPayments->join(CutOff::getTableName(), function ($q) {
                    $q->on(CutOff::getTableName('id'), '=', CutOffSalesDownPayment::getTableName('cut_off_id'));
                });
            }
        }

        $cutOffSalesDownPayments = pagination($cutOffSalesDownPayments, $request->get('limit'));

        return new ApiCollection($cutOffSalesDownPayments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSalesDownPaymentRequest $request
     * @return ApiResource
     */
    public function store(StoreSalesDownPaymentRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $customer = Customer::findOrFail($request->get('customer_id'));

        $cutOffId = CutOff::where('id', '>', 0)->orderBy('id', 'desc')->first()->id;

        $cutOffSalesDownPayment = new CutOffSalesDownPayment;
        $cutOffSalesDownPayment->cut_off_id = $cutOffId;
        $cutOffSalesDownPayment->chart_of_account_id = $request->get('chart_of_account_id');
        $cutOffSalesDownPayment->customer_id = $customer->id;
        $cutOffSalesDownPayment->notes = $request->get('notes');
        $cutOffSalesDownPayment->amount = $request->get('amount');
        $cutOffSalesDownPayment->save();

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffSalesDownPayment);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSalesDownPaymentRequest $request
     * @param int $id
     * @return ApiResource
     */
    public function update(UpdateSalesDownPaymentRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $cutOffSalesDownPayment = CutOffSalesDownPayment::findOrFail($id);
        $cutOffSalesDownPayment->chart_of_account_id = $request->get('chart_of_account_id');
        $cutOffSalesDownPayment->customer_id = $request->get('customer_id');
        $cutOffSalesDownPayment->notes = $request->get('notes');
        $cutOffSalesDownPayment->amount = $request->get('amount');
        $cutOffSalesDownPayment->save();

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffSalesDownPayment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        DB::connection('tenant')->beginTransaction();

        $cutOffSalesDownPayment = CutOffSalesDownPayment::findOrFail($id);

        $cutOffSalesDownPayment->delete();

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
