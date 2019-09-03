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
use App\Model\Marketplace\MarketplaceItemUnit;
use App\Model\Marketplace\MarketplaceItem;
use App\Model\Project\Project;

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

            /**
             * For market place item
             */
            if ($item->is_marketplace_item) {
                $marketplace_item_request = $request->only(['code', 'barcode', 'name', 'size', 'color', 'weight', 'notes', 'units']);
                $marketplace_item_request['item_id'] = $item->id;

                $project = Project::where('code', $request->header('Tenant'))->first();
                if ($project) {
                    $marketplace_item_request['project_id'] = $project->id;
                }
                
                $marketplace_item = MarketplaceItem::create($marketplace_item_request);
            }
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

                if ($item->is_marketplace_item) {
                    $marketplace_item['code'] = $item->code;
                    $marketplace_item['barcode'] = $item->barcode;
                    $marketplace_item['name'] = $item->name;
                    $marketplace_item['size'] = $item->size;
                    $marketplace_item['color'] = $item->color;
                    $marketplace_item['weight'] = $item->weight;
                    $marketplace_item['notes'] = $item->notes;
                    $marketplace_item['item_id'] = $item->id;
                    $marketplace_item['units'] = $item->units;

                    $project = Project::where('code', $request->header('Tenant'))->first();
                    if ($project) {
                        $marketplace_item_request['project_id'] = $project->id;
                    }
                    $marketplace_item = MarketplaceItem::create($marketplace_item);
                }

                array_push($collection, $item);
            }

            return new ApiCollection(collect($collection));
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
        $item = Item::eloquentFilter($request)->findOrFail($id);

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

        /** 
         * This code section is to save market place item on database
         * If you check visible in marketplace option for item, it should be shown on other's sight via marketplace.
         * To solve this, when you update an item, it will make(update) or remove same item on marketplace_item table
         */
        $marketplace_item = MarketplaceItem::where("item_id", $id)->first();
        if (!$marketplace_item) {
            if ($item->is_marketplace_item) {
                $marketplace_item_request = $request->only(['code', 'barcode', 'name', 'size', 'color', 'weight', 'notes', 'units']);
                $marketplace_item_request['item_id'] = $item->id;

                $project = Project::where('code', $request->header('Tenant'))->first();
                if ($project) {
                    $marketplace_item_request['project_id'] = $project->id;
                }

                $marketplace_item = MarketplaceItem::create($marketplace_item_request);
            }
        } else {
            if ($item->is_marketplace_item) {
                $marketplace_item_request = $request->only(['code', 'barcode', 'name', 'size', 'color', 'weight', 'notes', 'units']);
                $marketplace_item_request['item_id'] = $item->id;
                $project = Project::where('code', $request->header('Tenant'))->first();
                if ($project) {
                    $marketplace_item_request['project_id'] = $project->id;
                }
                $marketplace_item->fill($marketplace_item_request);
                $marketplace_item->save();   
            } else {
                $marketplace_item->delete();
            }
        }

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

            /** 
             * This code section is to save market place item on database
             * If you check visible in marketplace option for item, it should be shown on other's sight via marketplace.
             * To solve this, when you update an item, it will make(update) or remove same item on marketplace_item table
             */
            $marketplace_item = MarketplaceItem::where("item_id", $id)->first();
            if (!$marketplace_item) {
                if ($item->is_marketplace_item) {
                    $marketplace_item_request = $request->only(['code', 'barcode', 'name', 'size', 'color', 'weight', 'notes', 'units']);
                    $marketplace_item_request['item_id'] = $item->id;

                    $project = Project::where('code', $request->header('Tenant'))->first();
                    if ($project) {
                        $marketplace_item_request['project_id'] = $project->id;
                    }

                    $marketplace_item = MarketplaceItem::create($marketplace_item_request);
                }
            } else {
                if ($item->is_marketplace_item) {
                    $marketplace_item_request = $request->only(['code', 'barcode', 'name', 'size', 'color', 'weight', 'notes', 'units']);
                    $marketplace_item_request['item_id'] = $item->id;
                    $project = Project::where('code', $request->header('Tenant'))->first();
                    if ($project) {
                        $marketplace_item_request['project_id'] = $project->id;
                    }
                    $marketplace_item->fill($marketplace_item_request);
                    $marketplace_item->save();   
                } else {
                    $marketplace_item->delete();
                }
            }

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
    public function destroy(Request $request, $id)
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

        /**
         * Remove marketplace item
         */
        $project = Project::where('code', $request->header('Tenant'))->first();
        if ($project) {
            $marketplace_item = MarketplaceItem::where("item_id", $id)->where("project_id", $project->id)->first();
            if ($marketplace_item) {
                $marketplace_item->delete();
            }
        }

        return response()->json([], 204);
    }
}
