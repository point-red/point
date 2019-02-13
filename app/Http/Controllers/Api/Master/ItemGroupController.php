<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Resources\ApiResource;
use App\Model\Master\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ItemGroupController extends Controller
{
    /**
     * attach resource to the group.
     *
     * @param Request $request
     * @param $id
     * @request $groups
     * @return ApiResource
     */
    public function attach(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $item->groups()->attach($request->get('groups'));

        return new ApiResource($item);
    }

    /**
     * detach resource to the group.
     *
     * @param Request $request
     * @param $id
     * @request $groups
     * @return ApiResource
     */
    public function detach(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $item->groups()->detach($request->get('groups'));

        return new ApiResource($item);
    }

    /**
     * sync resource to the group.
     *
     * @param Request $request
     * @param $id
     * @request $groups
     * @return ApiResource
     */
    public function sync(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $item->groups()->sync($request->get('groups'));

        return new ApiResource($item);
    }
}
