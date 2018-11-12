<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Requests\HumanResource\Employee\EmployeeGroup\StoreEmployeeGroupRequest;
use App\Http\Requests\Master\Group\StoreGroupRequest;
use App\Http\Requests\Master\Group\UpdateGroupRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{
    private $availableGroupTypes = ['supplier', 'customer', 'item'];

    private $masterNamespace = 'App\Model\Master\\';

    private $groupTypeIsNotAvailableResponse = [
        'code' => 400,
        'message' => 'Group type is not available'
    ];

    private function isGroupTypeAvailable($groupType)
    {
        if (!in_array($groupType, $this->availableGroupTypes)) {
            return false;
        }

        return true;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $groupType = $request->get('type');

        if (!$this->isGroupTypeAvailable($groupType)) {
            return response()->json($this->groupTypeIsNotAvailableResponse);
        }

        $groups = Group::where('type', $this->masterNamespace . capitalize($groupType))
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

        if (!$this->isGroupTypeAvailable($groupType)) {
            return response()->json($this->groupTypeIsNotAvailableResponse);
        }

        $group = new Group;
        $group->name = $request->get('name');
        $group->type = $this->masterNamespace . capitalize($groupType);
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
        $groupType = $request->get('type');

        if (!$this->isGroupTypeAvailable($groupType)) {
            return response()->json($this->groupTypeIsNotAvailableResponse);
        }

        $group = Group::findOrFail($id);

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
        $groupType = $request->get('type');

        if (!$this->isGroupTypeAvailable($groupType)) {
            return response()->json($this->groupTypeIsNotAvailableResponse);
        }

        $group = Group::findOrFail($id);
        $group->name = $request->get('name');
        $group->type = $this->masterNamespace . capitalize($groupType);
        $group->save();

        return new ApiResource($group);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return ApiResource
     */
    public function destroy(Request $request, $id)
    {
        $groupType = $request->get('type');

        if (!$this->isGroupTypeAvailable($groupType)) {
            return response()->json($this->groupTypeIsNotAvailableResponse);
        }

        $group = Group::findOrFail($id);

        $group->delete();

        return response()->json([], 204);
    }
}
