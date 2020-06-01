<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Master\ServiceGroup\AttachRequest;
use App\Http\Requests\Master\ServiceGroup\StoreRequest;
use App\Http\Requests\Master\ServiceGroup\UpdateRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Service;
use App\Model\Master\ServiceGroup;
use Illuminate\Http\Request;

class ServiceGroupController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $groups = ServiceGroup::from(ServiceGroup::getTableName().' as '.ServiceGroup::$alias)->eloquentFilter($request);

        $groups = ServiceGroup::joins($groups, $request->get('join'));

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
        $group = new ServiceGroup;
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
        $group = ServiceGroup::from(ServiceGroup::getTableName().' as '.ServiceGroup::$alias)->eloquentFilter($request);

        $group = ServiceGroup::joins($group, $request->get('join'));

        $group = $group->where(ServiceGroup::$alias.'.id', $id)->first();

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
        $group = ServiceGroup::findOrFail($id);
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
        $group = ServiceGroup::findOrFail($id);
        $group->delete();

        return response()->noContent();
    }

    public function attach(AttachRequest $request)
    {
        $service = Service::findOrFail($request->get('service_id'));
        $service->groups()->attach($request->get('service_group_id'));

        return new ApiResource($service);
    }

    public function detach(AttachRequest $request)
    {
        $service = Service::findOrFail($request->get('service_id'));
        $service->groups()->detach($request->get('service_group_id'));

        return new ApiResource($service);
    }
}
