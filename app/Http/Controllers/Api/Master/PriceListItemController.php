<?php

namespace App\Http\Controllers\Api\Master;

use App\Model\Master\Item;
use Illuminate\Http\Request;
use App\Model\Master\ItemUnit;
use App\Model\Master\PricingGroup;
use App\Http\Resources\ApiResource;
use App\Model\Master\PriceListItem;
use App\Http\Controllers\Controller;
use App\Http\Resources\Master\PriceListCollection;

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
        $date = $request->get('date') ?? now();

        $pricingGroupId = $request->get('pricing_group_id');

        $priceListItem = ItemUnit::join('items', Item::getTableName().'.id', '=', ItemUnit::getTableName().'.item_id')
            ->eloquentFilter($request)
            ->with(['pricing' => function ($q) use ($pricingGroupId, $date) {
                $q->rightJoin(PricingGroup::getTableName(), PricingGroup::getTableName().'.id', '=', PriceListItem::getTableName().'.pricing_group_id');
                if ($pricingGroupId) {
                    $q->where(PricingGroup::getTableName().'.id', $pricingGroupId);
                }
                $q->where('date', '<=', $date)
                    ->select(PriceListItem::getTableName().'.price')
                    ->addSelect(PriceListItem::getTableName().'.discount_percent')
                    ->addSelect(PriceListItem::getTableName().'.discount_value')
                    ->addSelect('item_unit_id')
                    ->addSelect('pricing_group_id');
            }])->with('item')->select(ItemUnit::getTableName().'.*');

        $priceListItem = pagination($priceListItem, $request->get('limit'));

        return new PriceListCollection($priceListItem);
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
