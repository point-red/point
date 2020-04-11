<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Master\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::from('warehouses as ' . Warehouse::$alias)->eloquentFilter($request);

        $warehouses = Warehouse::joins($warehouses, $request->get('join'));

        if ($request->get('is_archived')) {
            $warehouses = $warehouses->whereNotNull('archived_at');
        } else {
            $warehouses = $warehouses->whereNull('archived_at');
        }

        $warehouses = pagination($warehouses, $request->get('limit'));

        return new ApiCollection($warehouses);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Master\Warehouse\StoreWarehouseRequest $request
     * @return ApiResource
     */
    public function store(StoreWarehouseRequest $request)
    {
        $warehouse = new Warehouse;
        $warehouse->fill($request->all());
        $warehouse->save();

        return new ApiResource($warehouse);
    }

    /**
     * Display the specified resource.
     *
     * @param  Request $request
     * @param  int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $warehouse = Warehouse::from('warehouses as ' . Warehouse::$alias)
            ->eloquentFilter($request);

        $warehouse = Warehouse::joins($warehouse, $request->get('join'))

        $warehouse = $warehouse->where('warehouse.id', $id)->first();

        return new ApiResource($warehouse);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function update(UpdateWarehouseRequest $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->fill($request->all());
        $warehouse->save();

        return new ApiResource($warehouse);
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
