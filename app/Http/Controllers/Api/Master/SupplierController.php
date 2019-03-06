<?php

namespace App\Http\Controllers\Api\Master;

use App\Model\Master\Bank;
use App\Model\Master\Email;
use App\Model\Master\Group;
use App\Model\Master\Phone;
use Illuminate\Http\Request;
use App\Model\Master\Address;
use App\Model\Master\Supplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Model\Master\ContactPerson;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Requests\Master\Supplier\StoreSupplierRequest;
use App\Http\Requests\Master\Supplier\UpdateSupplierRequest;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \App\Http\Resources\ApiCollection
     */
    public function index(Request $request)
    {
        $suppliers = Supplier::eloquentFilter($request);

        if ($request->has('group_id')) {
            $suppliers = $suppliers->leftJoin('groupables', 'groupables.groupable_id', '=', 'items.id')
                ->where('groupables.groupable_type', Supplier::class)
                ->where('groupables.group_id', '=', $request->get('group_id'));
        }

        $suppliers = pagination($suppliers, request()->get('limit'));

        return new ApiCollection($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Master\Supplier\StoreSupplierRequest $request
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function store(StoreSupplierRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $supplier = new Supplier;
        $supplier->fill($request->all());
        $supplier->save();

        if ($request->get('group')['name']) {
            $group = Group::find($request->get('group')['id']);

            if (! $group) {
                $group = new Group;
                $group->name = $request->get('group')['name'];
                $group->class_reference = Supplier::class;
                $group->save();
            }

            $group->suppliers()->attach($supplier);
        }

        Address::saveFromRelation($supplier, $request->get('addresses'));
        Phone::saveFromRelation($supplier, $request->get('phones'));
        Email::saveFromRelation($supplier, $request->get('emails'));
        ContactPerson::saveFromRelation($supplier, $request->get('contacts'));
        Bank::saveFromRelation($supplier, $request->get('banks'));

        DB::connection('tenant')->commit();

        return new ApiResource($supplier);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return \App\Http\Resources\ApiResource
     */
    public function show(Request $request, $id)
    {
        $suppliers = Supplier::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($suppliers);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Master\Supplier\UpdateSupplierRequest $request
     * @param $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function update(UpdateSupplierRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $supplier = Supplier::findOrFail($id);
        $supplier->fill($request->all());
        $supplier->save();

        if ($request->get('group')['name']) {
            $group = Group::find($request->get('group')['id']);

            if (! $group) {
                $group = new Group;
                $group->name = $request->get('group')['name'];
                $group->class_reference = Supplier::class;
                $group->save();
            }

            $group->suppliers()->attach($supplier);
        }

        Address::saveFromRelation($supplier, $request->get('addresses'));
        Phone::saveFromRelation($supplier, $request->get('phones'));
        Email::saveFromRelation($supplier, $request->get('emails'));
        ContactPerson::saveFromRelation($supplier, $request->get('contacts'));
        Bank::saveFromRelation($supplier, $request->get('banks'));

        DB::connection('tenant')->commit();

        return new ApiResource($supplier);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json([], 204);
    }
}
