<?php

namespace App\Helpers\Inventory;

use App\Model\Form;
use App\Model\Inventory\Inventory;

class InventoryHelper
{
    public static function insert($item, $totalAmount, $additionalFee, $form_id, $warehouse_id)
    {
        // TODO: Check if quantity is 0 then is not allowed

        $lastInventory = self::getLastReference($item->item_id, $warehouse_id);

        $inventory = new Inventory;
        $inventory->form_id = $form_id;
        $inventory->warehouse_id = $warehouse_id;
        $inventory->item_id = $item->item_id;
        $inventory->quantity = $item->quantity;

        $subtotal = ($item->price - $item->discount_value) * $item->quantity;
        $itemAdditionalFee = $subtotal / $totalAmount * $additionalFee;
        $inventory->price = $itemAdditionalFee / $item->quantity + $item->price - $item->discount_value;

        $inventory->total_quantity = $item->quantity;

        $lastTotalValue = 0;
        if ($lastInventory) {
            $inventory->total_quantity += $lastInventory->total_quantity;
            $lastTotalValue = $lastInventory->total_value;
        }
        // increase stock
        if ($item->quantity > 0) {
            $inventory->total_value = $item->quantity * $inventory->price + $lastTotalValue;
        }
        // decrease stock
        else {
            $inventory->total_value = $inventory->total_quantity * $lastInventory->cogs;
        }
        $inventory->cogs = $inventory->total_value / $inventory->total_quantity;

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
        return Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->orderBy('date', 'DESC')
            ->first();
    }
}
