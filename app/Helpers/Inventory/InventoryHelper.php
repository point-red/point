<?php

namespace App\Helpers\Inventory;

use App\Exceptions\ItemQuantityInvalidException;
use App\Exceptions\StockNotEnoughException;
use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Inventory\Inventory;

class InventoryHelper
{
    /**
     * @param $formId
     * @param $warehouseId
     * @param $itemId
     * @param $quantity
     * @param $price
     * @throws StockNotEnoughException
     * @throws ItemQuantityInvalidException
     */
    private static function insert($formId, $warehouseId, $itemId, $quantity, $price)
    {
        if ($quantity == 0) {
            throw new ItemQuantityInvalidException(Item::findOrFail($itemId));
        }

        $lastInventory = self::getLastReference($itemId, $warehouseId);

        $inventory = new Inventory;
        $inventory->form_id = $formId;
        $inventory->warehouse_id = $warehouseId;
        $inventory->item_id = $itemId;
        $inventory->quantity = $quantity;
        $inventory->price = $price;
        $inventory->total_quantity = $quantity;

        // check if stock is enough to prevent stock minus
        if ($quantity < 0 && (!$lastInventory || $lastInventory->total_quantity < $quantity)) {
            throw new StockNotEnoughException(Item::findOrFail($itemId));
        }

        $lastTotalValue = 0;
        if ($lastInventory) {
            $inventory->total_quantity += $lastInventory->total_quantity;
            $lastTotalValue = $lastInventory->total_value;
        }

        // if quantity > increase stock else decrease stock
        if ($quantity > 0) {
            $inventory->total_value = $lastTotalValue + ($quantity * $inventory->price);
        } else {
            $inventory->total_value = $lastTotalValue - ($quantity * $lastInventory->cogs);
        }

        if ($inventory->total_quantity > 0) {
            $inventory->cogs = $inventory->total_value / $inventory->total_quantity;
        } else {
            $inventory->cogs = $lastInventory->cogs;
        }

        $inventory->save();
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
