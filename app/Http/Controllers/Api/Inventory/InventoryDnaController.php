<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Inventory\Inventory;
use Illuminate\Http\Request;

class InventoryDnaController extends Controller
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
        $warehouseId = $request->get('warehouse_id');
        $inventories = Inventory::selectRaw('*, sum(quantity) as remaining')
            ->groupBy(['item_id', 'production_number', 'expiry_date'])
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->having('remaining', '>', 0)
            ->get();

        return new ApiCollection($inventories);
    }

    public function allDna(Request $request, $itemId)
    {
        $warehouseId = $request->get('warehouse_id');
        $inventories = Inventory::selectRaw('*, sum(quantity) as remaining')
            ->groupBy(['item_id', 'production_number', 'expiry_date'])
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->get();

        return new ApiCollection($inventories);
    }
}
