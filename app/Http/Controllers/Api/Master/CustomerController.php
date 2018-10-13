<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Requests\Master\Customer\StoreCustomerRequest;
use App\Http\Requests\Master\Customer\UpdateCustomerRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Customer;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function index()
    {
        return new ApiCollection(Customer::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = new Customer;
        $customer->fill($request->all());
        $customer->save();

        return new ApiResource($customer);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function show($id)
    {
        return new ApiResource(Customer::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int                     $id
     *
     * @return string
     */
    public function update(UpdateCustomerRequest $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->fill($request->all());
        $customer->save();

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
    }
}
