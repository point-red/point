<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseContract;

use Illuminate\Http\Request;
use App\Model\Master\Supplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Purchase\PurchaseContract\PurchaseContract;

class PurchaseContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $purchaseContracts = PurchaseContract::eloquentFilter($request)
            ->joinForm()
            ->join(Supplier::getTableName(), PurchaseContract::getTableName('supplier_id'), '=', Supplier::getTableName('id'))
            ->notArchived()
            ->with('form');

        $purchaseContracts = pagination($purchaseContracts, $request->get('limit'));

        return new ApiCollection($purchaseContracts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $purchaseContract = PurchaseContract::create($request->all());
            $purchaseContract
                ->load('form')
                ->load('supplier');

            return new ApiResource($purchaseContract);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return void
     */
    public function show(Request $request, $id)
    {
        $purchaseContract = PurchaseContract::eloquentFilter($request)
            ->with('form')
            ->findOrFail($id);

        return new ApiResource($purchaseContract);
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
