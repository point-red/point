<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Resources\ApiCollection;
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
            ->where('item_id', $request->get('item_id'))
            ->whereBetween('forms.date', [$request->get('date_from'), $request->get('date_to')]);

        $inventories = $inventories->paginate(100);

        return new ApiCollection($inventories);
    }
}
