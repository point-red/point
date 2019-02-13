<?php

namespace App\Helpers\Inventory;

use App\Model\Inventory\Inventory;

class InventoryHelper
{
    public static function insert($data)
    {
        // TODO: Check if quantity is 0 then is not allowed

        $lastInventory = self::getLastReference($data->item_id, $data->warehouse_id);

        $inventory = new Inventory;
        $inventory->form_id = $data->form_id;
        $inventory->warehouse_id = $data->warehouse_id;
        $inventory->item_id = $data->item_id;
        $inventory->quantity = $data->quantity;
        $inventory->price = $data->price;

        $inventory->total_quantity = $lastInventory->total_quantity += $data->quantity;
        if ($data->quantity > 0) {
            $inventory->total_value = $lastInventory->total_value += $data->quantity * $data->price;
            $inventory->cogs = $lastInventory->total_value / $inventory->total_quantity;
        } else {
            $inventory->total_value = $lastInventory->total_value += $data->quantity * $lastInventory->cogs;
            $inventory->cogs = $lastInventory->cogs;
        }

        $inventory->save();

        // TODO: add journal
    }

    /**
     * Get last reference from inventory
     * Usually we will used it for get last stock or value of some item in warehouse
     *
     * @param $item_id
     * @param $warehouse_id
     * @return mixed
     */
    private static function getLastReference($item_id, $warehouse_id)
    {
        return Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->orderBy('forms.date')
            ->first();
    }
}
