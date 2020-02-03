<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\Inventory\InventoryCollection;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $dateFrom = convert_to_server_timezone($request->get('date_from'));
        $dateTo = convert_to_server_timezone($request->get('date_to'));
        $warehouseId = $request->get('warehouse_id');

        $inventoryStart = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select('inventories.*')
            ->addSelect(DB::raw('sum(inventories.quantity) as totalQty'))
            ->where('forms.date', '<', $dateFrom)
            ->groupBy('inventories.item_id');

        if ($warehouseId) {
            $inventoryStart = $inventoryStart->where('warehouse_id', '=', $warehouseId);
        }

        $inventoryIn = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select('inventories.*')
            ->addSelect(DB::raw('sum(inventories.quantity) as totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->where('quantity', '>', 0)
            ->groupBy('inventories.item_id');

        if ($warehouseId) {
            $inventoryIn = $inventoryIn->where('warehouse_id', '=', $warehouseId);
        }

        $inventoryOut = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select('inventories.*')
            ->addSelect(DB::raw('sum(inventories.quantity) as totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->where('quantity', '<', 0)
            ->groupBy('inventories.item_id');

        if ($warehouseId) {
            $inventoryOut = $inventoryOut->where('warehouse_id', '=', $warehouseId);
        }

        $items = Item::leftJoinSub($inventoryIn, 'subQueryInventoryIn', function ($join) {
                $join->on('items.id', '=', 'subQueryInventoryIn.item_id');
            })->leftJoinSub($inventoryOut, 'subQueryInventoryOut', function ($join) {
                $join->on('items.id', '=', 'subQueryInventoryOut.item_id');
            })->leftJoinSub($inventoryStart, 'subQueryInventoryStart', function ($join) {
                $join->on('items.id', '=', 'subQueryInventoryStart.item_id');
            })
            ->select('items.*')
            ->addSelect('subQueryInventoryStart.totalQty as opening_balance')
            ->addSelect('subQueryInventoryIn.totalQty as stock_in')
            ->addSelect('subQueryInventoryOut.totalQty as stock_out')
            ->addSelect(DB::raw('subQueryInventoryStart.totalQty + subQueryInventoryIn.totalQty as ending_balance'));

        $items = pagination($items, $request->get('limit'));

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
