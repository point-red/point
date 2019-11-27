<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Master\CustomerGroup\AttachRequest;
use App\Http\Requests\Master\CustomerGroup\StoreCustomerGroupRequest;
use App\Http\Requests\Master\CustomerGroup\UpdateCustomerGroupRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Customer;
use App\Model\Master\CustomerGroup;
use Illuminate\Http\Request;

class CustomerGroupController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $groups = CustomerGroup::eloquentFilter($request);

        $groups = pagination($groups, $request->get('limit'));

        return new ApiCollection($groups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCustomerGroupRequest $request
     * @return ApiResource
     */
    public function store(StoreCustomerGroupRequest $request)
    {
        $group = new CustomerGroup;
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
        $group = CustomerGroup::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($group);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCustomerGroupRequest $request
     * @param $id
     * @return ApiResource
     */
    public function update(UpdateCustomerGroupRequest $request, $id)
    {
        $group = CustomerGroup::findOrFail($id);
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
        $group = CustomerGroup::findOrFail($id);
        $group->delete();

        return response()->noContent();
    }

    public function attach(AttachRequest $request)
    {
        $customer = Customer::findOrFail($request->get('customer_id'));
        $customer->groups()->attach($request->get('customer_group_id'));

        return new ApiResource($customer);
    }

    public function detach(AttachRequest $request)
    {
        $customer = Customer::findOrFail($request->get('customer_id'));
        $customer->groups()->detach($request->get('customer_group_id'));

        return new ApiResource($customer);
    }
}
