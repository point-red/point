<?php

namespace App\Http\Controllers\Api\Master;

use App\Model\Master\Bank;
use App\Model\Master\Email;
use App\Model\Master\Group;
use App\Model\Master\Phone;
use Illuminate\Http\Request;
use App\Model\Master\Address;
use App\Model\Master\Supplier;
use App\Model\Accounting\Journal;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Model\Master\ContactPerson;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Finance\Payment\Payment;
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

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('addresses', $fields)) {
                $suppliers = $suppliers->leftjoin(Address::getTableName(), function ($q) {
                    $q->on(Address::getTableName('addressable_id'), '=', Supplier::getTableName('id'))
                        ->where(Address::getTableName('addressable_type'), Supplier::class);
                });
            }

            if (in_array('phones', $fields)) {
                $suppliers = $suppliers->leftjoin(Phone::getTableName(), function ($q) {
                    $q->on(Phone::getTableName('phoneable_id'), '=', Supplier::getTableName('id'))
                        ->where(Phone::getTableName('phoneable_type'), Supplier::class);
                });
            }

            if (in_array('emails', $fields)) {
                $suppliers = $suppliers->leftjoin(Email::getTableName(), function ($q) {
                    $q->on(Email::getTableName('emailable_id'), '=', Supplier::getTableName('id'))
                        ->where(Email::getTableName('emailable_type'), Supplier::class);
                });
            }

            if (in_array('contact_persons', $fields)) {
                $suppliers = $suppliers->leftjoin(ContactPerson::getTableName(), function ($q) {
                    $q->on(ContactPerson::getTableName('contactable_id'), '=', Supplier::getTableName('id'))
                        ->where(ContactPerson::getTableName('contactable_type'), Supplier::class);
                });
            }

            if (in_array('banks', $fields)) {
                $suppliers = $suppliers->leftjoin(Bank::getTableName(), function ($q) {
                    $q->on(Bank::getTableName('bankable_id'), '=', Supplier::getTableName('id'))
                        ->where(Bank::getTableName('bankable_type'), Supplier::class);
                });
            }

            if (in_array('journals', $fields)) {
                $suppliers = $suppliers->leftjoin(Journal::getTableName(), function ($q) {
                    $q->on(Journal::getTableName('journalable_id'), '=', Supplier::getTableName('id'))
                        ->where(Journal::getTableName('journalable_type'), Supplier::class);
                });
            }

            if (in_array('payments', $fields)) {
                $suppliers = $suppliers->leftjoin(Payment::getTableName(), function ($q) {
                    $q->on(Payment::getTableName('paymentable_id'), '=', Supplier::getTableName('id'))
                        ->where(Payment::getTableName('paymentable_type'), Supplier::class);
                });
            }
        }

        if ($request->get('group_id')) {
            $suppliers = $suppliers->join('groupables', function ($q) use ($request) {
                $q->on('groupables.groupable_id', '=', 'suppliers.id')
                    ->where('groupables.groupable_type', Supplier::class)
                    ->where('groupables.group_id', '=', $request->get('group_id'));
            });
        }

        $suppliers = pagination($suppliers, $request->get('limit'));

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

        if ($request->has('group')) {
            $group = null;
            if (! empty($request->get('group')['id'])) {
                $group = Group::findOrFail($request->get('group')['id']);
            } elseif (! empty($request->get('group')['name'])) {
                $group = Group::where('name', $request->get('group')['name'])
                    ->where('class_reference', Supplier::class)
                    ->first();

                if (! $group) {
                    $group = new Group;
                    $group->name = $request->get('group')['name'];
                    $group->class_reference = 'supplier';
                    $group->save();
                }
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

        if ($request->has('group')) {
            $group = null;
            if (! empty($request->get('group')['id'])) {
                $group = Group::findOrFail($request->get('group')['id']);
            } elseif (! empty($request->get('group')['name'])) {
                $group = Group::where('name', $request->get('group')['name'])
                    ->where('class_reference', Supplier::class)
                    ->first();

                if (! $group) {
                    $group = new Group;
                    $group->name = $request->get('group')['name'];
                    $group->class_reference = 'supplier';
                    $group->save();
                }
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
