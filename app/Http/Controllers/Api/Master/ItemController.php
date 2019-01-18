<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Item\StoreItemRequest;
use App\Http\Requests\Master\Item\UpdateItemRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $items = Item::eloquentFilter($request);

        if ($request->get('group_id')) {
            $items = $items->leftJoin('groupables', 'groupables.groupable_id', '=', 'items.id')
                ->where('groupables.groupable_type', Item::class)
                ->where('groupables.group_id', '=', 1);
        }

        $items = pagination($items, $request->get('limit'));

        return new ApiCollection($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreItemRequest $request
     * @return \App\Http\Resources\ApiResource
     */
    public function store(StoreItemRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $item = new Item;
        $item->fill($request->all());
        $item->save();

        // TODO units is required and must be array
        $units = $request->get('units');
        $unitsToBeInserted = [];
        foreach ($units as $unit) {
            $itemUnit = new ItemUnit();
            $itemUnit->fill($unit);
            array_push($unitsToBeInserted, $itemUnit);
        }
        $item->units()->saveMany($unitsToBeInserted);

        // TODO groups is optional and must be array
        $groups = $request->get('groups');
        if (isset($groups)) {
            $item->groups()->attach($groups);
        }

        DB::connection('tenant')->commit();

        return new ApiResource($item);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreItemRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeMany(StoreItemRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $items = $request->get('items');

        foreach ($items as $item) {
            $newItem = new Item;
            $newItem->fill($item);
            $newItem->save();

            // TODO units is required and must be array
            $units = $item['units'];
            $unitsToBeInserted = [];
            foreach ($units as $unit) {
                $itemUnit = new ItemUnit();
                $itemUnit->fill($unit);
                array_push($unitsToBeInserted, $itemUnit);
            }
            $newItem->units()->saveMany($unitsToBeInserted);

            // TODO groups is optional and must be array
            $groups = $item['groups'];
            if (isset($groups)) {
                $newItem->groups()->attach($groups);
            }
        }

        DB::connection('tenant')->commit();

        return response()->json([], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $item = Item::eloquentFilter($request)
            ->with('groups')
            ->with('units')
            ->findOrFail($id);

        return new ApiResource($item);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateItemRequest $request
     * @param $id
     * @return ApiResource
     */
    public function update(UpdateItemRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $item = Item::findOrFail($id);
        $item->fill($request->all());
        $item->save();

        // TODO units is required and must be array
        $units = $request->get('units');
        $unitsToBeInserted = [];
        ItemUnit::where('item_id', $id)->whereNotIn('id', array_column($units, 'id'))->delete();
        foreach ($units as $unit) {
            if (isset($unit['id'])) {
                $itemUnit = ItemUnit::where('id', $unit['id'])->first();
            } else {
                $itemUnit = new ItemUnit();
            }
            $itemUnit->fill($unit);
            array_push($unitsToBeInserted, $itemUnit);
        }
        $item->units()->saveMany($unitsToBeInserted);

        // TODO groups is optional and must be array
        $groups = $request->get('groups');
        if (isset($groups)) {
            $item->groups()->sync($groups);
        }

        DB::connection('tenant')->commit();

        return new ApiResource($item);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMany(Request $request)
    {
        DB::connection('tenant')->beginTransaction();

        $items = $request->get('items');

        foreach ($items as $item) {
            $newItem = Item::findOrFail($item->id);
            $newItem->fill($item);
            $newItem->save();

            $units = $item['units'];
            $unitsToBeInserted = [];
            if ($units) {
                foreach ($units as $unit) {
                    $itemUnit = new ItemUnit();
                    $itemUnit->fill($unit);
                    array_push($unitsToBeInserted, $itemUnit);
                }
            }
            $newItem->units()->saveMany($unitsToBeInserted);

            $newItem->groups()->attach($item['groups']);
        }

        DB::connection('tenant')->commit();

        return response()->json([], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json([], 204);
    }
}
