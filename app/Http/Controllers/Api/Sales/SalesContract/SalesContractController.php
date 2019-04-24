<?php

namespace App\Http\Controllers\Api\Sales\SalesContract;

use Illuminate\Http\Request;
use App\Model\Master\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Sales\SalesContract\SalesContract;
use App\Http\Requests\Sales\SalesContract\SalesContract\StoreSalesContractRequest;
use App\Http\Requests\Sales\SalesContract\SalesContract\UpdateSalesContractRequest;

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
        $salesContracts = SalesContract::eloquentFilter($request);

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
        $salesContract = SalesContract::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($salesContract);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSalesContractRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function update(UpdateSalesContractRequest $request, $id)
    {
        $salesContract = SalesContract::with('form')->findOrFail($id);
        $salesContract->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $salesContract) {
            $salesContract->form->archive();
            $request['number'] = $salesContract->form->edited_number;

            $salesContract = SalesContract::create($request->all());
            $salesContract->load('form');

            return new ApiResource($salesContract);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $salesContract = SalesContract::findOrFail($id);
        $salesContract->isAllowedToDelete();

        $salesContract->requestCancel($request);

        return response()->json([], 204);
    }
}
