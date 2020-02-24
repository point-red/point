<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\CutOff\StoreInventoryRequest;
use App\Http\Requests\Accounting\CutOff\UpdateInventoryRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffInventory;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CutOffInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $cutOffInventories = CutOffInventory::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('item', $fields)) {
                $cutOffInventories = $cutOffInventories->join(Item::getTableName(), function ($q) {
                    $q->on(Item::getTableName('id'), '=', CutOffInventory::getTableName('item_id'));
                });
            }

            if (in_array('cutOff', $fields)) {
                $cutOffInventories = $cutOffInventories->join(CutOff::getTableName(), function ($q) {
                    $q->on(CutOff::getTableName('id'), '=', CutOffInventory::getTableName('cut_off_id'));
                });
            }
        }

        $cutOffInventories = pagination($cutOffInventories, $request->get('limit'));

        return new ApiCollection($cutOffInventories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInventoryRequest $request
     * @return ApiResource
     */
    public function store(StoreInventoryRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $item = Item::create($request->all());

        $cutOffId = CutOff::where('id', '>', 0)->orderBy('id', 'desc')->first()->id;

        // create cut off account
        foreach ($request->get('opening_stocks') as $openingStock) {
            $cutOffInventory = new CutOffInventory;
            $cutOffInventory->cut_off_id = $cutOffId;
            $cutOffInventory->chart_of_account_id = $item->chart_of_account_id;
            $cutOffInventory->item_id = $item->id;
            $cutOffInventory->warehouse_id = $openingStock['warehouse_id'];
            $cutOffInventory->unit = $request->get('unit');
            $cutOffInventory->converter = $request->get('converter');
            $cutOffInventory->expiry_date = $openingStock['expiry_date'];
            $cutOffInventory->production_number = $openingStock['production_number'];
            $cutOffInventory->quantity = $openingStock['quantity'];
            $cutOffInventory->price = $openingStock['price'];
            $cutOffInventory->total = $openingStock['price'] * $openingStock['quantity'];
            $cutOffInventory->save();
        }

        $itemUnit = new ItemUnit();
        $itemUnit->item_id = $cutOffInventory->item_id;
        $itemUnit->label = $cutOffInventory->unit;
        $itemUnit->name = $cutOffInventory->unit;
        $itemUnit->converter = $cutOffInventory->converter;
        $itemUnit->created_by = $cutOffInventory->cutOff->form->created_by;
        $itemUnit->updated_by = $cutOffInventory->cutOff->form->updated_by;
        $itemUnit->save();

        $cutOffInventory->item->unit_default = $itemUnit->id;
        $cutOffInventory->item->unit_default_purchase = $itemUnit->id;
        $cutOffInventory->item->unit_default_sales = $itemUnit->id;
        $cutOffInventory->item->save();

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffInventory);
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
     * @param UpdateInventoryRequest $request
     * @param int $id
     * @return ApiResource
     */
    public function update(UpdateInventoryRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $cutOffInventory = CutOffInventory::findOrFail($id);
        $cutOffInventory->chart_of_account_id = $request->get('chart_of_account_id');
        $cutOffInventory->warehouse_id = $request->get('warehouse_id');
        $cutOffInventory->unit = $request->get('unit');
        $cutOffInventory->converter = $request->get('converter');
        $cutOffInventory->expiry_date = $request->get('expiry_date');
        $cutOffInventory->production_number = $request->get('production_number');
        $cutOffInventory->quantity = $request->get('quantity');
        $cutOffInventory->price = $request->get('price');
        $cutOffInventory->total = $request->get('price') * $request->get('quantity');
        $cutOffInventory->save();

        $cutOffInventory->item->name = $request->get('name');
        $cutOffInventory->item->code = $request->get('code');
        $cutOffInventory->item->chart_of_account_id = $request->get('chart_of_account_id');
        $cutOffInventory->item->save();

        $itemUnit = new ItemUnit();
        $itemUnit->item_id = $cutOffInventory->item_id;
        $itemUnit->label = $cutOffInventory->unit;
        $itemUnit->name = $cutOffInventory->unit;
        $itemUnit->converter = $cutOffInventory->converter;
        $itemUnit->created_by = $cutOffInventory->cutOff->form->created_by;
        $itemUnit->updated_by = $cutOffInventory->cutOff->form->updated_by;
        $itemUnit->save();

        $cutOffInventory->item->unit_default = $itemUnit->id;
        $cutOffInventory->item->unit_default_purchase = $itemUnit->id;
        $cutOffInventory->item->unit_default_sales = $itemUnit->id;
        $cutOffInventory->item->save();

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffInventory);
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

        $cutOffInventory = CutOffInventory::findOrFail($id);

        $chartOfAccount = Item::findOrFail($cutOffInventory->item_id);

        $cutOffInventory->delete();

        $chartOfAccount->delete();

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
