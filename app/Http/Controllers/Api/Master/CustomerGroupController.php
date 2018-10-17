<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Requests\Master\CustomerGroup\StoreCustomerGroupRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\CustomerGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index()
    {
        $customerGroups = CustomerGroup::all();

        return new ApiCollection($customerGroups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCustomerGroupRequest $request
     * @return ApiResource
     */
    public function store(StoreCustomerGroupRequest $request)
    {
        $customerGroup = new CustomerGroup;
        $customerGroup->fill($request->all());
        $customerGroup->save();

        return new ApiResource($customerGroup);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show($id)
    {
        $customerGroup = CustomerGroup::findOrFail($id);

        return new ApiResource($customerGroup);
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
        $customerGroup = CustomerGroup::findOrFail($id);
        $customerGroup->fill($request->all());
        $customerGroup->save();

        return new ApiResource($customerGroup);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customerGroup = CustomerGroup::findOrFail($id);
        $customerGroup->delete();

        return response()->json([], 204);
    }
}
