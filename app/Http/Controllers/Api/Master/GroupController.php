<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{
    private $availableGroupTypes = ['supplier', 'customer', 'item'];

    private $masterNamespace = 'App\Model\Master\\';

    private function validateGroupType($groupType)
    {
        if (!in_array($groupType, $this->availableGroupTypes)) {
            return response()->json([
                'code' => 400,
                'message' => 'Group type is not available'
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $groupType = $request->get('group_type');

        $this->validateGroupType($groupType);

        $groups = Group::where('type', $this->masterNamespace . capitalize($groupType))->get();

        return new ApiCollection($groups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $groupType = $request->get('group_type');

        $this->validateGroupType($groupType);

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
        $groupType = $request->get('group_type');

        $this->validateGroupType($groupType);

        $group = Group::findOrFail($id);

        return new ApiResource($group);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        $groupType = $request->get('group_type');

        $this->validateGroupType($groupType);

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
        $groupType = $request->get('group_type');

        $this->validateGroupType($groupType);

        $group = Group::findOrFail($id);

        $group->delete();

        return response()->json([], 204);
    }
}
