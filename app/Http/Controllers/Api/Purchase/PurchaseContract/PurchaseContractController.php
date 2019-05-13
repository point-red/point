<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseContract;

use Illuminate\Http\Request;
use App\Model\Master\Supplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Purchase\PurchaseContract\PurchaseContract;
use App\Http\Requests\Purchase\PurchaseContract\StorePurchaseContractRequest;
use App\Http\Requests\Purchase\PurchaseContract\UpdatePurchaseContractRequest;

class PurchaseContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $purchaseContracts = PurchaseContract::eloquentFilter($request);

        $purchaseContracts = pagination($purchaseContracts, $request->get('limit'));

        return new ApiCollection($purchaseContracts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\Purchase\PurchaseContract\StorePurchaseContractRequest $request
     * @return App\Http\Resources\ApiResource
     * @throws \Throwable
     */
    public function store(StorePurchaseContractRequest $request)
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
        $purchaseContract = PurchaseContract::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($purchaseContract);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\Purchase\PurchaseContract\UpdatePurchaseContractRequest $request
     * @param  int  $id
     * @return App\Http\Resources\ApiResource
     */
    public function update(UpdatePurchaseContractRequest $request, $id)
    {
        $purchaseContract = PurchaseContract::findOrFail($id);
        $purchaseContract->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $purchaseContract) {
            $purchaseContract->form->archive();
            $request['number'] = $purchaseContract->form->edited_number;

            $purchaseContract = PurchaseContract::create($request->all());
            $purchaseContract
                ->load('form')
                ->load('supplier')
                ->load('items.item')
                ->load('groupItems.group');

            return new ApiResource($purchaseContract);
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
        $purchaseContract = PurchaseContract::findOrFail($id);
        $purchaseContract->isAllowedToDelete();

        $purchaseContract->requestCancel($request);

        return response()->json([], 204);
    }
}
