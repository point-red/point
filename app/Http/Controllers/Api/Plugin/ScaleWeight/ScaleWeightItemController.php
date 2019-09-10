<?php

namespace App\Http\Controllers\Api\Plugin\ScaleWeight;

use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Plugin\ScaleWeight\ScaleWeightItem;
use App\Http\Resources\Plugin\ScaleWeight\ScaleWeightItem\ScaleWeightItemResource;
use App\Http\Resources\Plugin\ScaleWeight\ScaleWeightItem\ScaleWeightItemCollection;
use App\Http\Requests\Plugin\ScaleWeight\ScaleWeightItem\StoreScaleWeightItemRequest;

class ScaleWeightItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ScaleWeightItemCollection
     */
    public function index(Request $request)
    {
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');

        $scaleWeightItem = ScaleWeightItem::whereBetween('time', [$date_from, $date_to])->paginate(100);

        return new ScaleWeightItemCollection($scaleWeightItem);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Plugin\ScaleWeight\ScaleWeightItem\StoreScaleWeightItemRequest $request
     * @return ApiResource
     */
    public function store(StoreScaleWeightItemRequest $request)
    {
        $scaleWeightItem = new ScaleWeightItem;
        $scaleWeightItem->uuid = $request->has('uuid') ? $request->get('uuid') : null;
        $scaleWeightItem->form_number = $request->get('form_number');
        $scaleWeightItem->license_number = $request->get('license_number');
        $scaleWeightItem->driver = $request->get('driver');
        $scaleWeightItem->machine_code = $request->get('machine_code');
        $scaleWeightItem->user = $request->get('user');
        $scaleWeightItem->vendor = $request->get('vendor');
        $scaleWeightItem->item = $request->get('item');
        $scaleWeightItem->time = $request->get('time');
        $scaleWeightItem->gross_weight = $request->get('gross_weight');
        $scaleWeightItem->tare_weight = $request->get('tare_weight');
        $scaleWeightItem->net_weight = $request->get('net_weight');
        $scaleWeightItem->save();

        return new ApiResource($scaleWeightItem);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $scaleWeightItem = ScaleWeightItem::findOrFail($id);

        $scaleWeightItem->license_number = $request->get('license_number');
        $scaleWeightItem->driver = $request->get('driver');
        $scaleWeightItem->machine_code = $request->get('machine_code');
        $scaleWeightItem->user = $request->get('user');
        $scaleWeightItem->vendor = $request->get('vendor');
        $scaleWeightItem->item = $request->get('item');
        $scaleWeightItem->time = $request->get('time');
        $scaleWeightItem->gross_weight = $request->get('gross_weight');
        $scaleWeightItem->tare_weight = $request->get('tare_weight');
        $scaleWeightItem->net_weight = $request->get('net_weight');
        $scaleWeightItem->save();

        return new ApiResource($scaleWeightItem);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $scaleWeightItem = ScaleWeightItem::findOrFail($id);
        $scaleWeightItem->delete();
        
        return response()->json([], 204);
    }
}
