<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inventory\InventoryCollection;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
}
