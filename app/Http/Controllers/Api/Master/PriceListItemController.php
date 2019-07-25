<?php

namespace App\Http\Controllers\Api\Master;

use App\Model\Master\Item;
use Illuminate\Http\Request;
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
        /* Get all available pricing groups */
        $availablePricingGroups = PricingGroup::select('id', 'label', 'notes')->get()->toArray();
        $date = $request->get('date') ?? date('Y-m-d H:i:s');

        $items = Item::eloquentFilter($request)->with('units.prices');
        $items = pagination($items, $request->get('limit'));

        $items->getCollection()->transform(function ($item) use ($date, $availablePricingGroups) {
            $units = $item->units->map(function ($unit) use ($date, $availablePricingGroups) {
                $priceGroups = $unit->prices
                    ->filter(function ($priceGroup) use ($date) {
                        /* Filter out price with date greater than $date */
                        return $priceGroup->pivot->date <= $date;
                    })
                    ->sortByDESC(function ($priceGroup) {
                        /* Sort by date, latest date on top */
                        return $priceGroup->pivot->date;
                    })
                    ->groupBy('id')
                    ->map(function ($priceGroup) {
                        /* Latest price group is still on the top */
                        /* Group them together then select the first price group */
                        return $priceGroup->first();
                    })
                    ->toArray();

                $endResultPriceGroups = [];

                /* Iterate through $availablePricingGroups and set its price */
                foreach ($availablePricingGroups as $availablePricingGroup) {
                    $price = 0;
                    $discount_value = 0;
                    $discount_percent = null;
                    $pricing_group_id = null;
                    foreach ($priceGroups as $priceGroup) {
                        if ($priceGroup['id'] == $availablePricingGroup['id']) {
                            $price = floatval($priceGroup['pivot']['price']);
                            $discount_value = floatval($priceGroup['pivot']['discount_value']);
                            $discount_percent = $priceGroup['pivot']['discount_percent'];
                            $pricing_group_id = $priceGroup['pivot']['pricing_group_id'];
                            if (! is_null($discount_percent)) {
                                $discount_percent = floatval($discount_percent);
                            }
                            break;
                        }
                    }
                    $endResultPriceGroups[] = $availablePricingGroup + [
                        'price' => $price,
                        'discount_value' => $discount_value,
                        'discount_percent' => $discount_percent,
                        'pricing_group_id' => $pricing_group_id,
                    ];
                }
                $unit = $unit->toArray();
                $unit['prices'] = $endResultPriceGroups;

                return $unit;
            });
            $item = $item->toArray();
            $item['units'] = $units;

            return $item;
        });

        return new PriceListCollection($items);
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
