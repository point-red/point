<?php

namespace App\Http\Controllers\Api\Master;

use App\Model\Master\Item;
use Illuminate\Http\Request;
use App\Model\Master\ItemUnit;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Requests\Master\Item\StoreItemRequest;
use App\Http\Requests\Master\Item\UpdateItemRequest;

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

        if ($request->has('group_id')) {
            $items = $items->leftJoin('groupables', 'groupables.groupable_id', '=', 'items.id')
                ->where('groupables.groupable_type', Item::class)
                ->where('groupables.group_id', '=', $request->get('group_id'));
        }

        if ($request->get('below_stock_reminder') == true) {
            $items = $items->whereRaw('items.stock < items.stock_reminder');
        }

        $items = pagination($items, $request->get('limit'));

        return new ApiCollection($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreItemRequest $request
     * @return \App\Http\Resources\ApiResource
     * @throws \Throwable
     */
    public function store(StoreItemRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $item = Item::create($request->all());
            $item->load('units', 'groups');

            return new ApiResource($item);
        });

        return $result;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function storeMany(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $items = $request->get('items');
            $collection = [];

            foreach ($items as $item) {
                $item = Item::create($item);
                $item->load('units', 'groups');

                $collection = array_merge($collection, $item);
            }

            return new ApiCollection($collection);
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
