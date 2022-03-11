<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Item\StoreItemRequest;
use App\Http\Requests\Master\Item\UpdateItemRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Item;
use App\Model\Master\ItemGroup;
use App\Model\Master\ItemUnit;
use App\Imports\Master\ItemImport;
use Maatwebsite\Excel\Facades\Excel;
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
        $items = Item::from(Item::getTableName().' as '.Item::$alias)->eloquentFilter($request);

        $items = Item::joins($items, $request->get('join'));

        if ($request->has('group_id')) {
            $items = $items->join('item_item_group', 'item_item_group.item_id', '=', 'item.id')
                ->where('item_item_group.item_group_id', '=', $request->get('group_id'));
        }

        if ($request->has('with_stock')) {
            $items = $items->leftJoin('inventories', 'inventories.item_id', '=', 'items.id')
                ->selectRaw('SUM(IFNULL(inventories.quantity, 0)) as stock')
                ->groupBy('items.id');

            if ($request->has('stock_below_stock_reminder')) {
                $items = $items->havingRaw('SUM(IFNULL(inventories.quantity, 0)) < stock_reminder');
            }

            if ($request->has('stock_above_zero')) {
                $items = $items->havingRaw('SUM(IFNULL(inventories.quantity, 0)) > 0');
            }
        }

        if ($request->get('is_archived')) {
            $items = $items->whereNotNull('archived_at');
        } else {
            $items = $items->whereNull('archived_at');
        }

        $items = pagination($items, $request->get('limit'));

        $id = DB::table('INFORMATION_SCHEMA.TABLES')
            ->select('AUTO_INCREMENT as id')
            ->where('TABLE_SCHEMA', env('DB_DATABASE', 'point').'_'.$request->header('Tenant'))
            ->where('TABLE_NAME', 'items')
            ->first();

        return (new ApiCollection($items))
            ->additional([
                'next_id' => $id->id,
            ]);
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

                array_push($collection, $item);
            }

            return new ApiCollection(collect($collection));
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
    public function import(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'name' => 'required',
            'chart_of_account' => 'required',
            'start_row' => 'required',
            'file' => 'required|mimes:xlsx,xls,csv|max:1024'
        ]);
        
        $result = new ItemImport;
        $result->startRow(request()->get("start_row"));
        Excel::import($result, request()->file('file'));

        return response()->json([
            'message' => 'success',
            'data' => $result->getResult()
        ], 200);
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
        $item = Item::from(Item::getTableName().' as '.Item::$alias)->eloquentFilter($request);

        $item = Item::joins($item, $request->get('join'));

        $item = $item->where(Item::$alias.'.id', $id)->first();

        $item->cogs = Item::cogs($item->id);

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

        if ($request->has('groups')) {
            $groups = $request->get('groups');
            foreach ($groups as $group) {
                if (! $group['id'] && $group['name']) {
                    $newGroup = new ItemGroup();
                    $newGroup->name = $group['name'];
                    $newGroup->save();
                    $group['id'] = $newGroup->id;
                }
            }
            $item->groups()->sync(array_column($groups, 'id'));
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
        $relationMethods = ['inventories'];

        $item = Item::findOrFail($id);

        foreach ($relationMethods as $relationMethod) {
            if ($item->$relationMethod()->count() > 0) {
                return response()->json([
                    'message' => 'Relation "'.$relationMethod.'" exists',
                ], 422);
            }
        }

        $item->delete();

        return response()->json([], 204);
    }
}
