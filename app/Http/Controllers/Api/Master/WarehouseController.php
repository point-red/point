<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Master\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
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
        $warehouses = Warehouse::eloquentFilter($request);

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
        $dateFrom = date('Y-m-d H:i:s', strtotime($request->get('date_from')));
        $dateTo = date('Y-m-d H:i:s', strtotime($request->get('date_to')));

        $warehouse = Warehouse::eloquentFilter($request)
                        ->with(['inventories' => function($query) use ($dateFrom, $dateTo) {
                            $query->with(['form' => function($query) use ($dateFrom, $dateTo) {
                                if ($dateFrom && $dateTo) {
                                    $query->whereBetween('date', [$dateFrom, $dateTo]);
                                    $query->sortBy('date');
                                }
                            }]);
                        }])->findOrFail($id);

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
