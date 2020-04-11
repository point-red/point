<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Master\AllocationGroup\AttachRequest;
use App\Http\Requests\Master\AllocationGroup\StoreRequest;
use App\Http\Requests\Master\AllocationGroup\UpdateRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Allocation;
use App\Model\Master\AllocationGroup;
use Illuminate\Http\Request;

class AllocationGroupController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $groups = AllocationGroup::from(AllocationGroup::getTableName() . ' as ' . AllocationGroup::$alias)
            ->eloquentFilter($request);

        $groups = pagination($groups, $request->get('limit'));

        return new ApiCollection($groups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return ApiResource
     */
    public function store(StoreRequest $request)
    {
        $group = new AllocationGroup;
        $group->fill($request->all());
        $group->save();

        return new ApiResource($group);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $group = AllocationGroup::from(AllocationGroup::getTableName() . ' as ' . AllocationGroup::$alias)
            ->eloquentFilter($request)
            ->where(AllocationGroup::$alias.'.id', $id)
            ->first();

        return new ApiResource($group);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param $id
     * @return ApiResource
     */
    public function update(UpdateRequest $request, $id)
    {
        $group = AllocationGroup::findOrFail($id);
        $group->fill($request->all());
        $group->save();

        return new ApiResource($group);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $group = AllocationGroup::findOrFail($id);
        $group->delete();

        return response()->noContent();
    }

    public function attach(AttachRequest $request)
    {
        $allocation = Allocation::findOrFail($request->get('allocation_id'));
        $allocation->groups()->attach($request->get('allocation_group_id'));

        return new ApiResource($allocation);
    }

    public function detach(AttachRequest $request)
    {
        $allocation = Allocation::findOrFail($request->get('allocation_id'));
        $allocation->groups()->detach($request->get('allocation_group_id'));

        return new ApiResource($allocation);
    }
}
