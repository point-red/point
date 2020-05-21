<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\HumanResource\Employee\EmployeeGroup\AttachRequest;
use App\Http\Requests\HumanResource\Employee\EmployeeGroup\StoreRequest;
use App\Http\Requests\HumanResource\Employee\EmployeeGroup\UpdateRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeGroup;
use Illuminate\Http\Request;

class EmployeeGroupController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $groups = EmployeeGroup::from(EmployeeGroup::getTableName() . ' as ' . EmployeeGroup::$alias)->eloquentFilter($request);

        $groups = EmployeeGroup::joins($groups, $request->get('join'));

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
        $group = new EmployeeGroup;
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
        $group = EmployeeGroup::from(EmployeeGroup::getTableName() . ' as ' . EmployeeGroup::$alias)->eloquentFilter($request);

        $group = EmployeeGroup::joins($group, $request->get('join'));

        $group = $group->where(EmployeeGroup::$alias.'.id', $id)->first();

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
        $group = EmployeeGroup::findOrFail($id);
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
        $group = EmployeeGroup::findOrFail($id);
        $group->delete();

        return response()->noContent();
    }

    public function attach(AttachRequest $request)
    {
        $customer = Employee::findOrFail($request->get('customer_id'));
        $customer->groups()->attach($request->get('customer_group_id'));

        return new ApiResource($customer);
    }

    public function detach(AttachRequest $request)
    {
        $customer = Employee::findOrFail($request->get('customer_id'));
        $customer->groups()->detach($request->get('customer_group_id'));

        return new ApiResource($customer);
    }
}
