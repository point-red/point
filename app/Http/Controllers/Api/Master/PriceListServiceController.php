<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Http\Resources\Master\PriceListCollection;
use App\Model\Master\PriceListService;
use App\Model\Master\PricingGroup;
use App\Model\Master\Service;
use Illuminate\Http\Request;

class PriceListServiceController extends Controller
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

        $services = Service::eloquentFilter($request)->with('prices');
        $services = pagination($services, $request->get('limit'));

        $pricingGroupId = null;

        if ($request->get('pricing_group_id')) {
            $pricingGroupId = $request->get('pricing_group_id');
            if ($pricingGroupId == -1) {
                if (count($availablePricingGroups) > 0) {
                    $pricingGroupId = $availablePricingGroups[0]['id'];
                } else {
                    $pricingGroupId = null;
                }
            }
        }

        $services->getCollection()->transform(function ($service) use ($date, $availablePricingGroups, $pricingGroupId) {
            $priceGroups = $service->prices
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
                    $shouldSetPrice = false;
                    if (! $pricingGroupId) {
                        if ($priceGroup['id'] == $availablePricingGroup['id']) {
                            $shouldSetPrice = true;
                        }
                    } else {
                        if ($priceGroup['id'] == $pricingGroupId && $availablePricingGroup['id'] == $pricingGroupId) {
                            $shouldSetPrice = true;
                        }
                    }
                    if ($shouldSetPrice) {
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

            $service = $service->toArray();
            $service['prices'] = $endResultPriceGroups;

            return $service;
        });

        return new PriceListCollection($services);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $priceListService = new PriceListService;
        $priceListService->fill($request->all());
        $priceListService->save();

        return new ApiResource($priceListService);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show($id)
    {
        $priceListService = PriceListService::from(PriceListService::getTableName() . ' as ' . PriceListService::$alias)->eloquentFilter($request);

        $priceListService = PriceListService::joins($priceListService, $request->get('join'));

        $priceListService = $priceListService->where(PriceListService::$alias.'.id', $id)->first();

        return new ApiResource($priceListService);
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
        $priceListService = PriceListService::findOrFail($id);
        $priceListService->fill($request->all());
        $priceListService->save();

        return new ApiResource($priceListService);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $priceListService = PriceListService::findOrFail($id);

        $priceListService->delete();

        return response()->json([], 204);
    }
}
