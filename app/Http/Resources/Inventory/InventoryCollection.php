<?php

namespace App\Http\Resources\Inventory;

use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Item;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class InventoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $dateFrom = Carbon::parse($request->get('date_from'), $request->header('Timezone'))
            ->timezone('UTC')
            ->toDateTimeString();

        $previous = Inventory::where('item_id', $request->get('item_id'))
            ->whereDate(Form::getTableName('date'), '<', $dateFrom)
            ->join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->select('total_quantity', 'total_value', 'cogs')
            ->orderBy('date', 'DESC')
            ->first();
        if (is_null($previous)) {
            $previous = [
                'total_quantity' => 0,
                'total_value' => 0,
                'cogs' => 0,
            ];
        }

        $item = Item::findOrFail($request->get('item_id'))->load('units', 'groups');

        return [
            'data' => [
                'item' => $item,
                'previous' => $previous,
                'record' => $this->collection,
            ],
        ];
    }
}
