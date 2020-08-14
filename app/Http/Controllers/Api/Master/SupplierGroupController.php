<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Master\SupplierGroup\AttachRequest;
use App\Http\Requests\Master\SupplierGroup\StoreRequest;
use App\Http\Requests\Master\SupplierGroup\UpdateRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Supplier;
use App\Model\Master\SupplierGroup;
use Illuminate\Http\Request;

class SupplierGroupController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $groups = SupplierGroup::from(SupplierGroup::getTableName().' as '.SupplierGroup::$alias)->eloquentFilter($request);

        $groups = SupplierGroup::joins($groups, $request->get('join'));

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
        $group = new SupplierGroup;
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
        $group = SupplierGroup::from(SupplierGroup::getTableName().' as '.SupplierGroup::$alias)->eloquentFilter($request);

        $group = SupplierGroup::joins($group, $request->get('join'));

        $group = $group->where(SupplierGroup::$alias.'.id', $id)->first();

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
        $group = SupplierGroup::findOrFail($id);
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
        $group = SupplierGroup::findOrFail($id);
        $group->delete();

        return response()->noContent();
    }

    public function attach(AttachRequest $request)
    {
        $supplier = Supplier::findOrFail($request->get('supplier_id'));
        $supplier->groups()->attach($request->get('supplier_group_id'));

        return new ApiResource($supplier);
    }

    public function detach(AttachRequest $request)
    {
        $supplier = Supplier::findOrFail($request->get('supplier_id'));
        $supplier->groups()->detach($request->get('supplier_group_id'));

        return new ApiResource($supplier);
    }
}
