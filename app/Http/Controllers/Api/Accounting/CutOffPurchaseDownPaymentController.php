<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\CutOff\StorePurchaseDownPaymentRequest;
use App\Http\Requests\Accounting\CutOff\UpdatePurchaseDownPaymentRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffPurchaseDownPayment;
use App\Model\Master\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CutOffPurchaseDownPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $cutOffPurchaseDownPayments = CutOffPurchaseDownPayment::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('supplier', $fields)) {
                $cutOffPurchaseDownPayments = $cutOffPurchaseDownPayments->join(Supplier::getTableName(), function ($q) {
                    $q->on(Supplier::getTableName('id'), '=', CutOffPurchaseDownPayment::getTableName('supplier_id'));
                });
            }

            if (in_array('chartOfAccount', $fields)) {
                $cutOffPurchaseDownPayments = $cutOffPurchaseDownPayments->join(ChartOfAccount::getTableName(), function ($q) {
                    $q->on(ChartOfAccount::getTableName('id'), '=', CutOffPurchaseDownPayment::getTableName('chart_of_account_id'));
                });
            }

            if (in_array('cutOff', $fields)) {
                $cutOffPurchaseDownPayments = $cutOffPurchaseDownPayments->join(CutOff::getTableName(), function ($q) {
                    $q->on(CutOff::getTableName('id'), '=', CutOffPurchaseDownPayment::getTableName('cut_off_id'));
                });
            }
        }

        $cutOffPurchaseDownPayments = pagination($cutOffPurchaseDownPayments, $request->get('limit'));

        return new ApiCollection($cutOffPurchaseDownPayments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePurchaseDownPaymentRequest $request
     * @return ApiResource
     */
    public function store(StorePurchaseDownPaymentRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $supplier = Supplier::findOrFail($request->get('supplier_id'));

        $cutOffId = CutOff::where('id', '>', 0)->orderBy('id', 'desc')->first()->id;

        $cutOffPurchaseDownPayment = new CutOffPurchaseDownPayment;
        $cutOffPurchaseDownPayment->cut_off_id = $cutOffId;
        $cutOffPurchaseDownPayment->chart_of_account_id = $request->get('chart_of_account_id');
        $cutOffPurchaseDownPayment->supplier_id = $supplier->id;
        $cutOffPurchaseDownPayment->notes = $request->get('notes');
        $cutOffPurchaseDownPayment->amount = $request->get('amount');
        $cutOffPurchaseDownPayment->save();

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffPurchaseDownPayment);
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
     * @param UpdatePurchaseDownPaymentRequest $request
     * @param int $id
     * @return ApiResource
     */
    public function update(UpdatePurchaseDownPaymentRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $cutOffPurchaseDownPayment = CutOffPurchaseDownPayment::findOrFail($id);
        $cutOffPurchaseDownPayment->chart_of_account_id = $request->get('chart_of_account_id');
        $cutOffPurchaseDownPayment->supplier_id = $request->get('supplier_id');
        $cutOffPurchaseDownPayment->notes = $request->get('notes');
        $cutOffPurchaseDownPayment->amount = $request->get('amount');
        $cutOffPurchaseDownPayment->save();

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffPurchaseDownPayment);
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

        $cutOffPurchaseDownPayment = CutOffPurchaseDownPayment::findOrFail($id);

        $cutOffPurchaseDownPayment->delete();

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
