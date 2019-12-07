<?php

namespace App\Http\Controllers\Api\Plugin\ScaleWeight;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plugin\ScaleWeight\ScaleWeightTruck\StoreRequest;
use App\Http\Requests\Plugin\ScaleWeight\ScaleWeightTruck\UpdateRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Plugin\ScaleWeight\ScaleWeightTruck;
use Illuminate\Http\Request;
use Zend\Diactoros\Response\EmptyResponse;

class ScaleWeightTruckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $scaleWeightTruck = ScaleWeightTruck::eloquentFilter($request);

        $scaleWeightTruck = pagination($scaleWeightTruck, $request->get('limit'));

        return new ApiCollection($scaleWeightTruck);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return ApiResource
     */
    public function store(StoreRequest $request)
    {
        $scaleWeightTruck = new ScaleWeightTruck;
        $scaleWeightTruck->form_number = $request->get('form_number');
        $scaleWeightTruck->machine_code = $request->get('machine_code');
        $scaleWeightTruck->uuid = $request->get('uuid');
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

        return new ApiResource($scaleWeightTruck);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show($id)
    {
        $scaleWeightTruck = ScaleWeightTruck::findOrFail($id);

        return new ApiResource($scaleWeightTruck);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return ApiResource
     */
    public function update(UpdateRequest $request, $id)
    {
        $scaleWeightTruck = ScaleWeightTruck::findOrFail($id);
        $scaleWeightTruck->form_number = $request->get('form_number');
        $scaleWeightTruck->machine_code = $request->get('machine_code');
        $scaleWeightTruck->uuid = $request->get('uuid') ?? null;
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

        return new ApiResource($scaleWeightTruck);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return EmptyResponse
     */
    public function destroy($id)
    {
        $scaleWeightTruck = ScaleWeightTruck::findOrFail($id);

        if ($scaleWeightTruck) {
            $scaleWeightTruck->delete();
        }

        return new EmptyResponse;
    }
}
