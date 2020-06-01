<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseReturn;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseReturn\PurchaseReturn;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $purchaseReturns = PurchaseReturn::from(PurchaseReturn::getTableName() . ' as ' . PurchaseReturn::$alias)->eloquentFilter($request);

        $purchaseReturns = PurchaseReturn::joins($purchaseReturns, $request->get('join'));

        $purchaseReturns = pagination($purchaseReturns, $request->get('limit'));

        return new ApiCollection($purchaseReturns);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $purchaseReturn = PurchaseReturn::create($request->all());
            $purchaseReturn->load('form', 'supplier', 'items', 'services');

            return new ApiResource($purchaseReturn);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        $purchaseReturn = PurchaseReturn::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($purchaseReturn);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws Throwable
     */
    public function update(Request $request, $id)
    {
        $purchaseInvoice = PurchaseReturn::findOrFail($id);
        $purchaseInvoice->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $purchaseReturn) {
            $purchaseReturn->form->archive();
            $request['number'] = $purchaseReturn->form->edited_number;
            $request['old_increment'] = $purchaseReturn->form->increment;

            $purchaseReturn = PurchaseReturn::create($request->all());
            $purchaseReturn->load([
                'form',
                'supplier',
                'items.item',
                'items.allocation',
                'services.service',
                'services.allocation',
            ]);

            return new ApiResource($purchaseReturn);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        DB::connection('tenant')->beginTransaction();

        $purchaseReturn = PurchaseReturn::findOrFail($id);
        $purchaseReturn->isAllowedToDelete();
        $purchaseReturn->requestCancel($request);

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
