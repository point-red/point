<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Requests\Master\Customer\StoreCustomerRequest;
use App\Http\Requests\Master\Customer\UpdateCustomerRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Address;
use App\Model\Master\Bank;
use App\Model\Master\ContactPerson;
use App\Model\Master\Customer;
use App\Http\Controllers\Controller;
use App\Model\Master\Email;
use App\Model\Master\Group;
use App\Model\Master\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \App\Http\Resources\ApiCollection
     */
    public function index(Request $request)
    {
        $customers = Customer::eloquentFilter($request)
            ->with('groups')
            ->with('addresses')
            ->with('emails')
            ->with('banks')
            ->with('phones')
            ->with('contactPersons');

        if ($request->get('group_id')) {
            $customers = $customers->leftJoin('groupables', 'groupables.groupable_id', '=', 'items.id')
                ->where('groupables.groupable_type', Customer::class)
                ->where('groupables.group_id', '=', 1);
        }

        $customers = $customers->paginate($request->get('paginate') ?? 20);

        return new ApiCollection($customers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Master\Customer\StoreCustomerRequest $request
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function store(StoreCustomerRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $customer = new Customer;
        $customer->fill($request->all());
        $customer->save();

        if ($request->get('group')['name']) {

            $group = Group::find($request->get('group')['id']);

            if (!$group) {
                $group = new Group;
                $group->name = $request->get('group')['name'];
                $group->type = Customer::class;
                $group->save();
            }

            $group->customers()->attach($customer);
        }

        Address::saveFromRelation($customer, $request->get('addresses'));
        Phone::saveFromRelation($customer, $request->get('phones'));
        Email::saveFromRelation($customer, $request->get('emails'));
        ContactPerson::saveFromRelation($customer, $request->get('contacts'));
        Bank::saveFromRelation($customer, $request->get('banks'));

        DB::connection('tenant')->commit();

        return new ApiResource($customer);
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
        $customers = Customer::eloquentFilter($request)
            ->with('groups')
            ->with('addresses')
            ->with('emails')
            ->with('banks')
            ->with('phones')
            ->with('contactPersons')
            ->findOrFail($id);

        return new ApiResource($customers);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Master\Customer\UpdateCustomerRequest $request
     * @param $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function update(UpdateCustomerRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $customer = Customer::findOrFail($id);
        $customer->fill($request->all());
        $customer->save();

        if ($request->get('group')['name']) {

            $group = Group::find($request->get('group')['id']);

            if (!$group) {
                $group = new Group;
                $group->name = $request->get('group')['name'];
                $group->type = Customer::class;
                $group->save();
            }

            $group->customers()->attach($customer);
        }

        Address::saveFromRelation($customer, $request->get('addresses'));
        Phone::saveFromRelation($customer, $request->get('phones'));
        Email::saveFromRelation($customer, $request->get('emails'));
        ContactPerson::saveFromRelation($customer, $request->get('contacts'));
        Bank::saveFromRelation($customer, $request->get('banks'));

        DB::connection('tenant')->commit();

        return new ApiResource($customer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([], 204);
    }
}
