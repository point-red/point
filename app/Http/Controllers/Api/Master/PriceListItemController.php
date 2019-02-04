<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Http\Resources\Master\PriceListCollection;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\PriceListItem;
use App\Model\Master\PricingGroup;
use Illuminate\Http\Request;

class PriceListItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return PriceListCollection
     */
    public function index(Request $request)
    {
        $items = Item::eloquentFilter($request)->with('units');

        $items = pagination($items, $request->get('limit'));

        $unitIds = $items->pluck('units')->flatten()->pluck('id');

        $priceListItems = \DB::connection('tenant')
            ->table(\DB::raw(PricingGroup::getTableName() . ',' . ItemUnit::getTableName()))
            ->leftJoin(PriceListItem::getTableName(), PriceListItem::getTableName('item_unit_id'), '=', ItemUnit::getTableName('id'))
            ->select(
                PricingGroup::getTableName('id') . ' AS pricing_group_id',
                PricingGroup::getTableName('label') . ' AS pricing_group_label',
                ItemUnit::getTableName('id') . ' AS unit_id',
                \DB::raw('IFNULL(' . PriceListItem::getTableName('price') . ', 0) AS price'),
                PriceListItem::getTableName('discount_percent'),
                PriceListItem::getTableName('discount_value'),
                PriceListItem::getTableName('date')
            )
            ->whereIn(ItemUnit::getTableName('id'), $unitIds)
            ->orderBy(PriceListItem::getTableName('pricing_group_id'), PriceListItem::getTableName('date'))
            ->get();

        foreach ($items as $key => $item) {
            foreach ($item->units as $key => $unit) {
                $unit_id = $unit->id;
                $unit['prices'] = $priceListItems->filter(function ($priceListItem, $key) use ($unit_id) {
                    return $priceListItem->unit_id === $unit_id;
                })->keyBy('pricing_group_id')->toArray();
            }
        }

        return new ApiCollection($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $priceListItem = new PriceListItem;
        $priceListItem->fill($request->all());
        $priceListItem->save();

        return new ApiResource($priceListItem);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show($id)
    {
        $priceListItem = PriceListItem::findOrFail($id);

        return new ApiResource($priceListItem);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        $priceListItem = PriceListItem::findOrFail($id);
        $priceListItem->fill($request->all());
        $priceListItem->save();

        return new ApiResource($priceListItem);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $priceListItem = PriceListItem::findOrFail($id);

        $priceListItem->delete();

        return response()->json([], 204);
    }
}
