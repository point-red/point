<?php

namespace App\Helpers\Inventory;

use App\Model\Form;
use App\Model\Inventory\Inventory;

class InventoryHelper
{
    public static function insert($formId, $warehouseId, $itemReference, $totalAmount, $additionalFee)
    {
        // TODO: Check if quantity is 0 then is not allowed

        $lastInventory = self::getLastReference($itemReference->item_id, $warehouseId);

        $inventory = new Inventory;
        $inventory->form_id = $formId;
        $inventory->warehouse_id = $warehouseId;
        $inventory->item_id = $itemReference->item_id;
        $inventory->quantity = $itemReference->quantity;

        $subtotal = ($itemReference->price - $itemReference->discount_value) * $itemReference->quantity;
        $itemReferenceAdditionalFee = $subtotal / $totalAmount * $additionalFee;
        $inventory->price = $itemReferenceAdditionalFee / $itemReference->quantity + $itemReference->price - $itemReference->discount_value;

        $inventory->total_quantity = $itemReference->quantity;

        $lastTotalValue = 0;
        if ($lastInventory) {
            $inventory->total_quantity += $lastInventory->total_quantity;
            $lastTotalValue = $lastInventory->total_value;
        }
        // increase stock
        if ($itemReference->quantity > 0) {
            $inventory->total_value = $itemReference->quantity * $inventory->price + $lastTotalValue;
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
