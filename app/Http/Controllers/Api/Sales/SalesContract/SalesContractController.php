<?php

namespace App\Http\Controllers\Api\Sales\SalesContract;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesContract\SalesContract\StoreSalesContractRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Customer;
use App\Model\Sales\SalesContract\SalesContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $salesContracts = SalesContract::eloquentFilter($request)
            ->join(Customer::getTableName(), SalesContract::getTableName('customer_id'), '=', Customer::getTableName('id'))
            ->select(SalesContract::getTableName('*'))
            ->joinForm()
            ->notArchived()
            ->with('form');

        $salesContracts = pagination($salesContracts, $request->get('limit'));

        return new ApiCollection($salesContracts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreSalesContractRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(StoreSalesContractRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $salesContract = SalesContract::create($request->all());
            $salesContract
                ->load('form')
                ->load('customer');

            return new ApiResource($salesContract);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  Request $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $salesContract = SalesContract::joinForm()
            ->eloquentFilter($request)
            ->findOrFail($id);

        return new ApiResource($salesContract);
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
