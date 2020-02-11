<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\CutOff\StoreAccountPayableRequest;
use App\Http\Requests\Accounting\CutOff\UpdateAccountPayableRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffAccountPayable;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CutOffAccountPayableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $cutOffInventories = CutOffAccountPayable::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('item', $fields)) {
                $cutOffInventories = $cutOffInventories->join(Item::getTableName(), function ($q) {
                    $q->on(Item::getTableName('id'), '=', CutOffAccountPayable::getTableName('item_id'));
                });
            }

            if (in_array('cutOff', $fields)) {
                $cutOffInventories = $cutOffInventories->join(CutOff::getTableName(), function ($q) {
                    $q->on(CutOff::getTableName('id'), '=', CutOffAccountPayable::getTableName('cut_off_id'));
                });
            }
        }

        $cutOffInventories = pagination($cutOffInventories, $request->get('limit'));

        return new ApiCollection($cutOffInventories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAccountPayableRequest $request
     * @return ApiResource
     */
    public function store(StoreAccountPayableRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $supplier = new Supplier;
        $supplier->fill($request->all());
        $supplier->save();

        $cutOffId = CutOff::where('id', '>', 0)->orderBy('id', 'desc')->first()->id;

        $cutOffAccountPayable = new CutOffAccountPayable;
        $cutOffAccountPayable->cut_off_id = $cutOffId;
        $cutOffAccountPayable->chart_of_account_id = $request->get('chart_of_account_id');
        $cutOffAccountPayable->supplier_id = $supplier->id;
        $cutOffAccountPayable->notes = $request->get('notes');
        $cutOffAccountPayable->amount = $request->get('amount');
        $cutOffAccountPayable->save();

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffAccountPayable);
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
     * @param UpdateAccountPayableRequest $request
     * @param int $id
     * @return ApiResource
     */
    public function update(UpdateAccountPayableRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $cutOffAccountPayable = CutOffAccountPayable::findOrFail($id);
        $cutOffAccountPayable->chart_of_account_id = $request->get('chart_of_account_id');
        $cutOffAccountPayable->supplier_id = $request->get('supplier_id');
        $cutOffAccountPayable->notes = $request->get('notes');
        $cutOffAccountPayable->amount = $request->get('amount');
        $cutOffAccountPayable->save();

        $cutOffAccountPayable->supplier->name = $request->get('name');

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffAccountPayable);
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

        $cutOffAccountPayable = CutOffAccountPayable::findOrFail($id);

        $cutOffAccountPayable->delete();

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
