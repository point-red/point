<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Master\GroupType;
use App\Http\Requests\Master\Group\StoreGroupRequest;
use App\Http\Requests\Master\Group\UpdateGroupRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $groupType = $request->get('type');

        if (!GroupType::isAvailable($groupType)) {
            return response()->json(GroupType::$isNotAvailableResponse);
        }

        $groups = Group::where('type', GroupType::getTypeClass($groupType))
            ->eloquentFilter($request)
            ->paginate($request->get('limit') ?? 20);

        return new ApiCollection($groups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiResource
     */
    public function store(StoreGroupRequest $request)
    {
        $groupType = $request->get('type');

        if (!GroupType::isAvailable($groupType)) {
            return response()->json(GroupType::$isNotAvailableResponse);
        }

        $group = Group::create($request->all());

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
        $group = Group::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($group);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateGroupRequest $request
     * @param  int $id
     * @return ApiResource
     */
    public function update(UpdateGroupRequest $request, $id)
    {
        $group = Group::findOrFail($id);
        $group->fill($request->all());
        $group->save();

        return new ApiResource($group);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        $group->delete();

        return response()->json([], 204);
    }
}
