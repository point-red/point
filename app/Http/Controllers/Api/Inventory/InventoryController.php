<?php

namespace App\Http\Controllers\Api\Inventory;

use Carbon\Carbon;
use App\Model\Form;
use Illuminate\Http\Request;
use App\Model\Inventory\Inventory;
use App\Http\Controllers\Controller;
use App\Model\Master\Item;
use App\Http\Resources\Inventory\InventoryCollection;
use App\Http\Resources\Master\Item\ItemCollection;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return InventoryCollection
     */
    public function index(Request $request)
    {
        $dateFrom = Carbon::parse($request->get('date_from'), $request->header('Timezone'))
            ->timezone('UTC')
            ->toDateTimeString();
        $dateTo = Carbon::parse($request->get('date_to'), $request->header('Timezone'))
            ->timezone('UTC')
            ->toDateTimeString();

        $inventories = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $request->get('item_id'))
            ->whereBetween(Form::getTableName('date'), [$dateFrom, $dateTo])
            ->select(Inventory::getTableName('*'))
            ->with('form');

        $inventories = $inventories->paginate(100);

        return new InventoryCollection($inventories);
    }

    public function getStock(Request $request)
    {

        $request->validate([
            'warehouse' => 'required|integer',
        ]);

        $query = Inventory::select(Item::getTableName().'.name')
            ->selectRaw("SUM(quantity) as quantity, warehouse_id as warehouse, item_id as item_id")
            ->where('warehouse_id', $request->get('warehouse'))
            ->join(Item::getTableName(), Item::getTableName().'.id', Inventory::getTableName().'.item_id')
            ->groupBy('item_id');

        return new ItemCollection($query->paginate(100));
    }
}
