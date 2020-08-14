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
        $allocations = Allocation::from(Allocation::getTableName().' as '.Allocation::$alias)->eloquentFilter($request);

        $allocations = Allocation::joins($allocations, $request->get('join'));

        if ($request->get('is_archived')) {
            $allocations = $allocations->whereNotNull('archived_at');
        } else {
            $allocations = $allocations->whereNull('archived_at');
        }

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
     * @param Request $request
     * @param int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $allocation = Allocation::from(Allocation::getTableName().' as '.Allocation::$alias)->eloquentFilter($request);

        $allocation = Allocation::joins($allocation, $request->get('join'));

        $allocation = $allocation->where(Allocation::$alias.'.id', $id)->first();

        return new ApiResource($allocation);
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
