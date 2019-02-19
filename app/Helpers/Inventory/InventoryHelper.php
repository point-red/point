<?php

namespace App\Helpers\Inventory;

use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Item;

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
        $inventory->cogs = $inventory->total_value / $inventory->total_quantity;

        $inventory->save();

        // TODO: add journal
    }

    public static function increase($formId, $warehouseId, $itemReference, $totalAmount, $additionalFee)
    {
        Item::where('id', $itemReference->item_id)->increment('stock', $itemReference->quantity);

        $subtotal = ($itemReference->price - $itemReference->discount_value) * $itemReference->quantity;
        $itemReferenceAdditionalFee = $subtotal / $totalAmount * $additionalFee;
        $price = $itemReferenceAdditionalFee / $itemReference->quantity + $itemReference->price - $itemReference->discount_value;

        self::insert($formId, $warehouseId, $itemReference->item_id, abs($itemReference->quantity), $price);
    }

    public static function decrease($formId, $warehouseId, $itemReference)
    {
        Item::where('id', $itemReference->item_id)->decrement('stock', $itemReference->quantity);

        self::insert($formId, $warehouseId, $itemReference->item_id, -abs($itemReference->quantity), $itemReference->price);
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
