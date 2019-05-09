<?php

namespace App\Helpers\Inventory;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Inventory\Inventory;

class InventoryHelper
{
    private static function insert($formId, $warehouseId, $itemId, $quantity, $price)
    {
        // TODO: Check if quantity is 0 then is not allowed
        $lastInventory = self::getLastReference($itemId, $warehouseId);

        $inventory = new Inventory;
        $inventory->form_id = $formId;
        $inventory->warehouse_id = $warehouseId;
        $inventory->item_id = $itemId;
        $inventory->quantity = $quantity;
        $inventory->price = $price;
        $inventory->total_quantity = $quantity;

        $lastTotalValue = 0;
        if ($lastInventory) {
            $inventory->total_quantity += $lastInventory->total_quantity;
            $lastTotalValue = $lastInventory->total_value;
        }
        // increase stock
        if ($quantity > 0) {
            $inventory->total_value = $quantity * $inventory->price + $lastTotalValue;
        }
        // decrease stock
        else {
            $inventory->total_value = $inventory->total_quantity * $lastInventory->cogs;
        }

        if ($inventory->total_quantity > 0) {
            $inventory->cogs = $inventory->total_value / $inventory->total_quantity;
        } else {
            $inventory->cogs = $lastInventory->cogs;
        }

        $inventory->save();

        // TODO: add journal
    }

    public static function increase($formId, $warehouseId, $itemId, $quantity, $price)
    {
        Item::where('id', $itemId)->increment('stock', $quantity);

        self::insert($formId, $warehouseId, $itemId, abs($quantity), $price);
    }

    public static function decrease($formId, $warehouseId, $itemId, $quantity)
    {
        Item::where('id', $itemId)->decrement('stock', $quantity);

        self::insert($formId, $warehouseId, $itemId, -abs($quantity), 0);
    }

    /**
     * Get last reference from inventory
     * Usually we will used it for get last stock or value of some item in warehouse.
     *
     * @param $itemId
     * @param $warehouseId
     * @return mixed
     */
    private static function getLastReference($itemId, $warehouseId)
    {
        return Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('date', 'DESC')
            ->first();
    }
}
