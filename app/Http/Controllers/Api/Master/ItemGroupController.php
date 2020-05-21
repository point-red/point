<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ItemGroup\StoreItemGroupRequest;
use App\Http\Requests\Master\ItemGroup\StoreRequest;
use App\Http\Requests\Master\ItemGroup\UpdateItemGroupRequest;
use App\Http\Requests\Master\ItemGroup\AttachRequest;
use App\Http\Requests\Master\ItemGroup\UpdateRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\ItemGroup;
use App\Model\Master\Item;
use Illuminate\Http\Request;

class ItemGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $groups = ItemGroup::from(ItemGroup::getTableName() . ' as ' . ItemGroup::$alias)->eloquentFilter($request);

        $groups = ItemGroup::joins($groups, $request->get('join'));

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
        $group = new ItemGroup;
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
        $group = ItemGroup::from(ItemGroup::getTableName() . ' as ' . ItemGroup::$alias)->eloquentFilter($request);

        $group = ItemGroup::joins($group, $request->get('join'));

        $group = $group->where(ItemGroup::$alias.'.id', $id)->first();

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
        $group = ItemGroup::findOrFail($id);
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
        $group = ItemGroup::findOrFail($id);
        $group->delete();

        return response()->noContent();
    }

    /**
     * attach resource to the group.
     *
     * @param AttachRequest $request
     * @return ApiResource
     * @request $groups
     */
    public function attach(AttachRequest $request)
    {
        $item = Item::with('groups')->findOrFail($request->get('item_id'));
        $item->groups()->attach($request->get('item_group_id'));

        return new ApiResource($item);
    }

    /**
     * detach resource to the group.
     *
     * @param AttachRequest $request
     * @return ApiResource
     * @request $groups
     */
    public function detach(AttachRequest $request)
    {
        $item = Item::with('groups')->findOrFail($request->get('item_id'));
        $item->groups()->detach($request->get('item_group_id'));

        return new ApiResource($item);
    }

    /**
     * sync resource to the group.
     *
     * @param AttachRequest $request
     * @return ApiResource
     * @request $groups
     */
    public function sync(AttachRequest $request)
    {
        $item = Item::with('groups')->findOrFail($request->get('item_id'));
        $item->groups()->sync($request->get('groups'));

        return new ApiResource($item);
    }
}
