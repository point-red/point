<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\PriceListItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PriceListItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $date = $request->get('date') ?? now();

        $ids = PriceListItem::selectRaw('max(date) as date')
            ->addSelect('id')
            ->where('date', '<=', $date)
            ->where('pricing_group_id','=', $request->get('pricing_group_id'))
            ->groupBy('item_unit_id')
            ->pluck('id');

        $priceListItem = PriceListItem::with('itemUnit.item')->whereIn('id', $ids);

        $priceListItem = pagination($priceListItem, $request->get('limit'));

        return new ApiCollection($priceListItem);
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
