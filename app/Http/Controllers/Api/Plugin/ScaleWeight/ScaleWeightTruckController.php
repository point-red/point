<?php

namespace App\Http\Controllers\Api\Plugin\ScaleWeight;

use App\Http\Resources\Plugin\ScaleWeight\ScaleWeightTruck\ScaleWeightTruckCollection;
use App\Http\Resources\Plugin\ScaleWeight\ScaleWeightTruck\ScaleWeightTruckResource;
use App\Model\Plugin\ScaleWeight\ScaleWeightTruck;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScaleWeightTruckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\Plugin\ScaleWeight\ScaleWeightTruck\ScaleWeightTruckCollection
     */
    public function index()
    {
        $scaleWeightTruck = ScaleWeightTruck::all();
        return new ScaleWeightTruckCollection($scaleWeightTruck);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\Plugin\ScaleWeight\ScaleWeightTruck\ScaleWeightTruckResource
     */
    public function store(Request $request)
    {
        $scaleWeightTruck = new ScaleWeightTruck;
        $scaleWeightTruck->form_number = $request->get('form_number');
        $scaleWeightTruck->machine_code = $request->get('machine_code');
        $scaleWeightTruck->license_number = $request->get('license_number');
        $scaleWeightTruck->driver = $request->get('driver');
        $scaleWeightTruck->user = $request->get('user');
        $scaleWeightTruck->vendor = $request->get('vendor');
        $scaleWeightTruck->item = $request->get('item');
        $scaleWeightTruck->time_in = $request->get('time_in');
        $scaleWeightTruck->time_out = $request->get('time_out');
        $scaleWeightTruck->gross_weight = $request->get('gross_weight');
        $scaleWeightTruck->tare_weight = $request->get('tare_weight');
        $scaleWeightTruck->net_weight = $request->get('net_weight');
        $scaleWeightTruck->is_delivery = $request->get('is_delivery');
        $scaleWeightTruck->save();

        return new ScaleWeightTruckResource($scaleWeightTruck);
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
