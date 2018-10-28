<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Requests\Master\Item\StoreItemRequest;
use App\Http\Requests\Master\Item\UpdateItemRequest;
use App\Http\Controllers\Controller;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\Group;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index()
    {
        $items = Item::eloquentFilter(request())
            ->with('groups')
            ->with('units')
            ->paginate(request()->get('paginate') ?? 20);

        return new ApiCollection($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Master\Item\StoreItemRequest $request
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function store(StoreItemRequest $request)
    {
        /**
         * $request params
         * 1. items Array
         * 2. groups Array
         * 3. group Object
         * 4. units Array
         * 5. unit Object
         * 6. item fillable
         */
        DB::connection('tenant')->transaction(
            function () use ($request) {
                $items = [];
                if ($request->get('items')) {
                    $items = $request->get('items');
                } else {
                    array_push($items, $request->all());
                }

                foreach ($items as $item) {
                    $newItem = new Item;
                    $newItem->fill($item);
                    $newItem->save(); // this will trigger INSERT for each item

                    $units = [];
                    if (array_key_exists('units', $item)) {
                        $units = $item['units'];
                    } elseif (array_key_exists('unit', $item)) {
                        array_push($units, $item['unit']);
                    }
                    $newUnits = [];
                    foreach ($units as $unit) {
                        $newUnit = new ItemUnit;
                        $newUnit->fill($unit);
                        array_push($newUnits, $newUnit);
                    }
                    $newItem->units()->saveMany($newUnits);

                    $groups = [];
                    if (array_key_exists('groups', $item)) {
                        $groups = $item['groups'];
                    } elseif (array_key_exists('group', $item)) {
                        array_push($groups, $item['group']);
                    }
                    $newGroups = [];
                    foreach ($groups as $group) {
                        if (!array_key_exists('id', $group)) {
                            $newGroup = Group::firstOrCreate([
                                'name' => $group['name'],
                                'type' => Item::class
                            ]);
                        } else {
                            $newGroup = Group::find($group['id']);
                        }
                        array_push($newGroups, $newGroup);
                    }
                    $newItem->groups()->attach(array_column($newGroups, 'id'));
                }
            }
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show($id)
    {
        $item = Item::eloquentFilter(request())
            ->with('groups')
            ->with('units')
            ->findOrFail($id);

        return new ApiResource($item);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Master\Item\UpdateItemRequest $request
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function update(UpdateItemRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $item = Item::findOrFail($id);
        $item->fill($request->all());
        $item->save();

        $units = $request->get('units');
        $unitsToBeInserted = [];
        if ($units) {
            foreach ($units as $unit) {
                $itemUnit = new ItemUnit();
                $itemUnit->fill($unit);
                array_push($unitsToBeInserted, $itemUnit);
            }
        }
        $item->units()->saveMany($unitsToBeInserted);

        DB::connection('tenant')->commit();

        return new ApiResource($item);
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
