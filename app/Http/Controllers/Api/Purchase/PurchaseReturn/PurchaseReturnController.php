<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseReturn;

use App\Http\Resources\ApiCollection;
use App\Model\Master\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Model\Purchase\PurchaseReturn\PurchaseReturn;
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
        $purchaseReturns = PurchaseReturn::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('supplier', $fields)) {
                $purchaseReturns = $purchaseReturns->join(Supplier::getTableName(), function ($q) {
                    $q->on(Supplier::getTableName('id'), '=', PurchaseReturn::getTableName('supplier_id'));
                });
            }

            if (in_array('form', $fields)) {
                $purchaseReturns = $purchaseReturns->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', PurchaseReturn::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), PurchaseReturn::$morphName);
                });
            }
        }

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
        $purchaseReturn = PurchaseReturn::findOrFail($id);
        $purchaseReturn->isAllowedToDelete();

        $response = $purchaseReturn->requestCancel($request);

        if (! $response) {
            foreach ($purchaseReturn->purchaseInvoices as $purchaseInvoice) {
                $purchaseInvoice->form->done = false;
                $purchaseInvoice->form->save();
            }
        }

        return response()->json([], 204);
    }
}
