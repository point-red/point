<?php

namespace App\Http\Controllers\Api\Plugin\ScaleWeight;

use App\Http\Resources\Plugin\ScaleWeight\ScaleWeightItem\ScaleWeightItemCollection;
use App\Http\Resources\Plugin\ScaleWeight\ScaleWeightItem\ScaleWeightItemResource;
use App\Model\Plugin\ScaleWeight\ScaleWeightItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScaleWeightItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\Plugin\ScaleWeight\ScaleWeightItem\ScaleWeightItemCollection
     */
    public function index()
    {
        $scaleWeightItem = ScaleWeightItem::all();
        return new ScaleWeightItemCollection($scaleWeightItem);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Plugin\ScaleWeight\ScaleWeightItem\ScaleWeightItemResource
     */
    public function store(Request $request)
    {
        $scaleWeightItem = new ScaleWeightItem;
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

        return new ScaleWeightItemResource($scaleWeightItem);
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
