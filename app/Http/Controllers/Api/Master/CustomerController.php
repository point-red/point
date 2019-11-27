<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Customer\StoreCustomerRequest;
use App\Http\Requests\Master\Customer\UpdateCustomerRequest;
use App\Http\Requests\Master\CustomerGroup\AttachRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\Journal;
use App\Model\Finance\Payment\Payment;
use App\Model\Master\Address;
use App\Model\Master\Bank;
use App\Model\Master\ContactPerson;
use App\Model\Master\Customer;
use App\Model\Master\Email;
use App\Model\Master\Group;
use App\Model\Master\Phone;
use Illuminate\Database\QueryException;
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
        $customers = Customer::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('addresses', $fields)) {
                $customers = $customers->leftjoin(Address::getTableName(), function ($q) {
                    $q->on(Address::getTableName('addressable_id'), '=', Customer::getTableName('id'))
                        ->where(Address::getTableName('addressable_type'), Customer::$morphName);
                });
            }

            if (in_array('phones', $fields)) {
                $customers = $customers->leftjoin(Phone::getTableName(), function ($q) {
                    $q->on(Phone::getTableName('phoneable_id'), '=', Customer::getTableName('id'))
                        ->where(Phone::getTableName('phoneable_type'), Customer::$morphName);
                });
            }

            if (in_array('emails', $fields)) {
                $customers = $customers->leftjoin(Email::getTableName(), function ($q) {
                    $q->on(Email::getTableName('emailable_id'), '=', Customer::getTableName('id'))
                        ->where(Email::getTableName('emailable_type'), Customer::$morphName);
                });
            }

            if (in_array('contact_persons', $fields)) {
                $customers = $customers->leftjoin(ContactPerson::getTableName(), function ($q) {
                    $q->on(ContactPerson::getTableName('contactable_id'), '=', Customer::getTableName('id'))
                        ->where(ContactPerson::getTableName('contactable_type'), Customer::$morphName);
                });
            }

            if (in_array('banks', $fields)) {
                $customers = $customers->leftjoin(Bank::getTableName(), function ($q) {
                    $q->on(Bank::getTableName('bankable_id'), '=', Customer::getTableName('id'))
                        ->where(Bank::getTableName('bankable_type'), Customer::$morphName);
                });
            }

            if (in_array('journals', $fields)) {
                $customers = $customers->leftjoin(Journal::getTableName(), function ($q) {
                    $q->on(Journal::getTableName('journalable_id'), '=', Customer::getTableName('id'))
                        ->where(Journal::getTableName('journalable_type'), Customer::$morphName);
                });
            }

            if (in_array('payments', $fields)) {
                $customers = $customers->leftjoin(Payment::getTableName(), function ($q) {
                    $q->on(Payment::getTableName('paymentable_id'), '=', Customer::getTableName('id'))
                        ->where(Payment::getTableName('paymentable_type'), Customer::$morphName);
                });
            }
        }

        if ($request->get('group_id')) {
            $customers = $customers->leftJoin('groupables', function ($q) use ($request) {
                $q->on('groupables.groupable_id', '=', 'customers.id')
                    ->where('groupables.groupable_type', Customer::$morphName)
                    ->where('groupables.group_id', '=', $request->get('group_id'));
            });
        }

        $customers = pagination($customers, $request->get('limit'));

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

        if ($request->has('group')) {
            $group = null;
            if (! empty($request->get('group')['id'])) {
                $group = Group::findOrFail($request->get('group')['id']);
            } elseif (! empty($request->get('group')['name'])) {
                $group = Group::where('name', $request->get('group')['name'])
                    ->where('class_reference', Customer::class)
                    ->first();

                if (! $group) {
                    $group = new Group;
                    $group->name = $request->get('group')['name'];
                    $group->class_reference = 'customer';
                    $group->save();
                }
            }

            if ($group) {
                $group->customers()->attach($customer);
            }
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
        $customer = Customer::eloquentFilter($request)->findOrFail($id);

        if ($request->get('total_payable')) {
            $customer->total_payable = $customer->totalAccountPayable();
        }
        if ($request->get('total_receivable')) {
            $customer->total_receivable = $customer->totalAccountReceivable();
        }

        return new ApiResource($customer);
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

        if ($request->has('group')) {
            $group = null;
            if (! empty($request->get('group')['id'])) {
                $group = Group::findOrFail($request->get('group')['id']);
            } elseif (! empty($request->get('group')['name'])) {
                $group = Group::where('name', $request->get('group')['name'])
                    ->where('class_reference', Customer::class)
                    ->first();

                if (! $group) {
                    $group = new Group;
                    $group->name = $request->get('group')['name'];
                    $group->class_reference = 'customer';
                    $group->save();
                }
            }

            if ($group) {
                $group->customers()->attach($customer);
            }
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

        try {
            $customer->delete();
        } catch (QueryException $e) {
            $customer->disabled = true;
            $customer->save();
        }

        return response()->json([], 204);
    }
}
