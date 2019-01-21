<?php

namespace App\Http\Controllers\Api\Inventory\InventoryAudit;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Inventory\InventoryAudit\InventoryAudit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InventoryAuditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index()
    {
        $inventoryAudits = InventoryAudit::eloquentFilter($request)
            ->select(InventoryAudit::getTableName('*'))
            ->joinForm()
            ->notArchived()
            ->with('form');

        $inventoryAudits = pagination($inventoryAudits, $request->get('limit'));

        return new ApiCollection($inventoryAudits);
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
            $inventoryAudit = InventoryAudit::create($request->all());
            $inventoryAudit->load('form')
                ->load('warehouse')
                ->load('items.item');

            return new ApiResource($inventoryAudit);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show($id)
    {
        $inventoryAudits = InventoryAudit::eloquentFilter($request)
            ->with('form')
            ->with('warehouse')
            ->with('items.item')
            ->findOrFail($id);

        return new ApiResource($inventoryAudits);
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
