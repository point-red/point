<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\Inventory\InventoryCollection;
use App\Model\Inventory\Inventory;
use App\Model\Master\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryRecapitulationController extends Controller
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

        $inventoryStart = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select('inventories.*')
            ->addSelect(DB::raw('sum(inventories.quantity) as totalQty'))
            ->where('forms.date', '<', $dateFrom)
            ->groupBy('inventories.item_id');

        $inventoryIn = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select('inventories.*')
            ->addSelect(DB::raw('sum(inventories.quantity) as totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->where('quantity', '>', 0)
            ->groupBy('inventories.item_id');

        $inventoryOut = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select('inventories.*')
            ->addSelect(DB::raw('sum(inventories.quantity) as totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->where('quantity', '<', 0)
            ->groupBy('inventories.item_id');

        $items = Item::eloquentFilter($request)->leftJoinSub($inventoryIn, 'subQueryInventoryIn', function ($join) {
            $join->on('items.id', '=', 'subQueryInventoryIn.item_id');
        })->leftJoinSub($inventoryOut, 'subQueryInventoryOut', function ($join) {
            $join->on('items.id', '=', 'subQueryInventoryOut.item_id');
        })->leftJoinSub($inventoryStart, 'subQueryInventoryStart', function ($join) {
            $join->on('items.id', '=', 'subQueryInventoryStart.item_id');
        })
            ->select('items.*')
            ->addSelect(DB::raw('COALESCE(subQueryInventoryStart.totalQty, 0) as opening_balance'))
            ->addSelect(DB::raw('COALESCE(subQueryInventoryIn.totalQty, 0) as stock_in'))
            ->addSelect(DB::raw('COALESCE(subQueryInventoryOut.totalQty, 0) as stock_out'))
            ->addSelect(DB::raw('COALESCE(subQueryInventoryStart.totalQty, 0) + COALESCE(subQueryInventoryIn.totalQty, 0) + COALESCE(subQueryInventoryOut.totalQty, 0) as ending_balance'));

        $items = pagination($items, $request->get('limit'));

        return new ApiCollection($items);
    }
}
