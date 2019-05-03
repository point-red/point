<?php

namespace App\Http\Controllers\Api\Sales\SalesDownPayment;

use App\Model\Form;
use App\Model\Master\Customer;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;

class SalesDownPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $downPayment = SalesDownPayment::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('customer', $fields)) {
                $downPayment->join(Customer::getTableName(), function ($q) {
                    $q->on(Customer::getTableName('id'), '=', SalesDownPayment::getTableName('customer_id'));
                });
            }

            if (in_array('form', $fields)) {
                $downPayment->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', SalesDownPayment::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), SalesDownPayment::class);
                });
            }
        }
        
        $downPayment = pagination($downPayment, $request->get('limit'));

        return new ApiCollection($downPayment);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = \DB::connection('tenant')->transaction(function () use ($request) {
            $downPayment = SalesDownPayment::create($request->all());
            $downPayment->load('form', 'customer');

            return new ApiResource($downPayment);
        });

        return $result;
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
        $downPayment = SalesDownPayment::eloquentFilter($request)
            ->with('form')
            ->findOrFail($id);

        return new ApiResource($downPayment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
