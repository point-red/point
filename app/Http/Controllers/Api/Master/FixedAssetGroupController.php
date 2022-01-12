<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\FixedAssetGroup\StoreFixedAssetGroupRequest;
use App\Http\Requests\Master\FixedAssetGroup\UpdateFixedAssetGroupRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\FixedAssetGroup;
use Illuminate\Http\Request;

class FixedAssetGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $groups = FixedAssetGroup::from(FixedAssetGroup::getTableName().' as '.FixedAssetGroup::$alias)->eloquentFilter($request);

        $groups = FixedAssetGroup::joins($groups, $request->get('join'));

        $groups = pagination($groups, $request->get('limit'));

        return new ApiCollection($groups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return ApiResource
     */
    public function store(StoreFixedAssetGroupRequest $request)
    {
        $group = new FixedAssetGroup;
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
        $group = FixedAssetGroup::from(FixedAssetGroup::getTableName().' as '.FixedAssetGroup::$alias)->eloquentFilter($request);

        $group = FixedAssetGroup::joins($group, $request->get('join'));

        $group = $group->where(FixedAssetGroup::$alias.'.id', $id)->first();

        return new ApiResource($group);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param $id
     * @return ApiResource
     */
    public function update(UpdateFixedAssetGroupRequest $request, $id)
    {
        $group = FixedAssetGroup::findOrFail($id);
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
        $group = FixedAssetGroup::findOrFail($id);
        $group->delete();

        return response()->noContent();
    }
}
