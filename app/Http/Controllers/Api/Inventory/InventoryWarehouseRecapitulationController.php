<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\Inventory\InventoryCollection;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryWarehouseRecapitulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param $itemId
     * @return ApiCollection
     */
    public function index(Request $request, $itemId)
    {
        $dateFrom = convert_to_server_timezone($request->get('date_from'));
        $dateTo = convert_to_server_timezone($request->get('date_to'));

        $inventoryStart = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select('inventories.*')
            ->addSelect(DB::raw('sum(inventories.quantity) as totalQty'))
            ->where('forms.date', '<', $dateFrom)
            ->where('item_id', '=', $itemId)
            ->groupBy('inventories.warehouse_id');

        $inventoryIn = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select('inventories.*')
            ->addSelect(DB::raw('sum(inventories.quantity) as totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->where('quantity', '>', 0)
            ->where('item_id', '=', $itemId)
            ->groupBy('inventories.warehouse_id');

        $inventoryOut = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select('inventories.*')
            ->addSelect(DB::raw('sum(inventories.quantity) as totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->where('quantity', '<', 0)
            ->where('item_id', '=', $itemId)
            ->groupBy('inventories.warehouse_id');

        $items = Warehouse::eloquentFilter($request)->leftJoinSub($inventoryIn, 'subQueryInventoryIn', function ($join) {
            $join->on('warehouses.id', '=', 'subQueryInventoryIn.warehouse_id');
        })->leftJoinSub($inventoryOut, 'subQueryInventoryOut', function ($join) {
            $join->on('warehouses.id', '=', 'subQueryInventoryOut.warehouse_id');
        })->leftJoinSub($inventoryStart, 'subQueryInventoryStart', function ($join) {
            $join->on('warehouses.id', '=', 'subQueryInventoryStart.warehouse_id');
        })->select('warehouses.*')
            ->addSelect(DB::raw('COALESCE(subQueryInventoryStart.totalQty, 0) as opening_balance'))
            ->addSelect(DB::raw('COALESCE(subQueryInventoryIn.totalQty, 0) as stock_in'))
            ->addSelect(DB::raw('COALESCE(subQueryInventoryOut.totalQty, 0) as stock_out'))
            ->addSelect(DB::raw('COALESCE(subQueryInventoryStart.totalQty, 0) + COALESCE(subQueryInventoryIn.totalQty, 0) + COALESCE(subQueryInventoryOut.totalQty, 0) as ending_balance'));


        $items = pagination($items, 10);

        return new ApiCollection($items);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $itemId
     * @return InventoryCollection
     */
    public function show(Request $request, $itemId)
    {
        $request->item_id = $itemId;
        $inventories = Inventory::eloquentFilter($request)
            ->join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->select(Inventory::getTableName('*'));

        if ($request->has('warehouse_id')) {
            $inventories = $inventories->where('warehouse_id', $request->get('warehouse_id'));
        }

        $inventories = $inventories->orderBy('forms.date', 'asc');

        $inventories = pagination($inventories, $request->get('limit'));

        $inventoryCollection = new InventoryCollection($inventories);

        $inventoryCollection->limit($request->get('limit'));
        $inventoryCollection->currentPage($request->get('page'));

        if ($request->filter_date_min) {
            $filterMin = convert_javascript_object_to_array($request->filter_date_min);
            if (array_has($filterMin, 'form.date')) {
                $inventoryCollection->dateFrom($filterMin['form.date']);
            }
        }

        if ($request->filter_date_max) {
            $filterMax = convert_javascript_object_to_array($request->filter_date_max);
            if (array_has($filterMax, 'form.date')) {
                $inventoryCollection->dateTo($filterMax['form.date']);
            }
        }

        return $inventoryCollection;
    }

    public function dna(Request $request, $itemId)
    {
        $inventories = Inventory::selectRaw('*, sum(quantity) as remaining')
            ->groupBy(['item_id', 'production_number', 'expiry_date'])
            ->where('item_id', $itemId)
            ->having('remaining', '>', 0)
            ->get();

        return new ApiCollection($inventories);
    }
}
