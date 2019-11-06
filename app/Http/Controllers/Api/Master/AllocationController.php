<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Allocation\StoreAllocationRequest;
use App\Http\Requests\Master\Allocation\UpdateAllocationRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Allocation;
use Illuminate\Http\Request;

class AllocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $allocations = Allocation::eloquentFilter($request);

        $allocations = pagination($allocations, $request->get('limit'));

        return new ApiCollection($allocations);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAllocationRequest $request
     * @return ApiResource
     */
    public function store(StoreAllocationRequest $request)
    {
        $allocation = new Allocation();
        $allocation->name = $request->input('name');
        $allocation->save();

        return new ApiResource($allocation);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show($id)
    {
        return new ApiResource(Allocation::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAllocationRequest $request
     * @param  int $id
     * @return ApiResource
     */
    public function update(UpdateAllocationRequest $request, $id)
    {
        $allocation = Allocation::findOrFail($id);
        $allocation->name = $request->input('name');
        $allocation->save();

        return new ApiResource($allocation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Allocation::findOrFail($id)->delete();

        return response(null, 204);
    }
}
