<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Model\Master\Warehouse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Master\Warehouse\WarehouseResource;
use App\Http\Resources\Master\Warehouse\WarehouseCollection;
use App\Http\Requests\Master\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Master\Warehouse\UpdateWarehouseRequest;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Master\Warehouse\WarehouseCollection
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::eloquentFilter($request);

        $warehouses = pagination($warehouses, $request->get('limit'));

        return new WarehouseCollection($warehouses);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Master\Warehouse\StoreWarehouseRequest $request
     *
     * @return \App\Http\Resources\Master\Warehouse\WarehouseResource
     */
    public function store(StoreWarehouseRequest $request)
    {
        $warehouse = new Warehouse;
        $warehouse->code = $request->input('code');
        $warehouse->name = $request->input('name');
        $warehouse->address = $request->input('address');
        $warehouse->phone = $request->input('phone');
        $warehouse->save();

        return new WarehouseResource($warehouse);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \App\Http\Resources\Master\Warehouse\WarehouseResource
     */
    public function show($id)
    {
        return new WarehouseResource(Warehouse::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \App\Http\Resources\Master\Warehouse\WarehouseResource
     */
    public function update(UpdateWarehouseRequest $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->code = $request->input('code');
        $warehouse->name = $request->input('name');
        $warehouse->address = $request->input('address');
        $warehouse->phone = $request->input('phone');
        $warehouse->save();

        return new WarehouseResource($warehouse);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Warehouse::findOrFail($id)->delete();

        return response(null, 204);
    }
}
