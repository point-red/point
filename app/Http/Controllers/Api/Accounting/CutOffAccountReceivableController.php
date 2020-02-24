<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\CutOff\StoreAccountReceivableRequest;
use App\Http\Requests\Accounting\CutOff\UpdateAccountReceivableRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffAccountReceivable;
use App\Model\Master\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CutOffAccountReceivableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $cutOffAccountReceivables = CutOffAccountReceivable::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('customer', $fields)) {
                $cutOffAccountReceivables = $cutOffAccountReceivables->join(Supplier::getTableName(), function ($q) {
                    $q->on(Supplier::getTableName('id'), '=', CutOffAccountReceivable::getTableName('customer_id'));
                });
            }

            if (in_array('chartOfAccount', $fields)) {
                $cutOffAccountReceivables = $cutOffAccountReceivables->join(ChartOfAccount::getTableName(), function ($q) {
                    $q->on(ChartOfAccount::getTableName('id'), '=', CutOffAccountReceivable::getTableName('chart_of_account_id'));
                });
            }

            if (in_array('cutOff', $fields)) {
                $cutOffAccountReceivables = $cutOffAccountReceivables->join(CutOff::getTableName(), function ($q) {
                    $q->on(CutOff::getTableName('id'), '=', CutOffAccountReceivable::getTableName('cut_off_id'));
                });
            }
        }

        $cutOffAccountReceivables = pagination($cutOffAccountReceivables, $request->get('limit'));

        return new ApiCollection($cutOffAccountReceivables);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAccountReceivableRequest $request
     * @return ApiResource
     */
    public function store(StoreAccountReceivableRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $customer = Supplier::findOrFail($request->get('customer_id'));

        $cutOffId = CutOff::where('id', '>', 0)->orderBy('id', 'desc')->first()->id;

        $cutOffAccountReceivable = new CutOffAccountReceivable;
        $cutOffAccountReceivable->cut_off_id = $cutOffId;
        $cutOffAccountReceivable->chart_of_account_id = $request->get('chart_of_account_id');
        $cutOffAccountReceivable->customer_id = $customer->id;
        $cutOffAccountReceivable->notes = $request->get('notes');
        $cutOffAccountReceivable->amount = $request->get('amount');
        $cutOffAccountReceivable->save();

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffAccountReceivable);
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
     * @param UpdateAccountReceivableRequest $request
     * @param int $id
     * @return ApiResource
     */
    public function update(UpdateAccountReceivableRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $cutOffAccountReceivable = CutOffAccountReceivable::findOrFail($id);
        $cutOffAccountReceivable->chart_of_account_id = $request->get('chart_of_account_id');
        $cutOffAccountReceivable->customer_id = $request->get('customer_id');
        $cutOffAccountReceivable->notes = $request->get('notes');
        $cutOffAccountReceivable->amount = $request->get('amount');
        $cutOffAccountReceivable->save();

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffAccountReceivable);
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

        $cutOffAccountReceivable = CutOffAccountReceivable::findOrFail($id);

        $cutOffAccountReceivable->delete();

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
