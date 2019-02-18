<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        $inventories = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->groupBy('item_id')
            ->orderBy('forms.date', 'desc');

        // Ignore item that doesn't have any stock
        if ($request->get('ignore_empty') && $request->get('ignore_empty') == true) {
            $inventories = $inventories->where('total_quantity', '>', 0);
        }

        $inventories = $inventories->get();

        return new ApiCollection($inventories);
    }

    public function show(Request $request, $itemId)
    {
        $inventory = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->groupBy('item_id')
            ->orderBy('forms.date', 'desc')
            ->first();

        return new ApiResource($inventory);
    }
}
