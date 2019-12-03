<?php

namespace App\Helpers\Inventory;

use App\Exceptions\ItemQuantityInvalidException;
use App\Exceptions\StockNotEnoughException;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Item;
use App\Model\Master\ItemDetail;

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
    private static function insert($formId, $warehouseId, $itemId, $itemUnitId, $productionNumber, $expiryDate, $quantity, $price)
    {
        if ($quantity == 0) {
            throw new ItemQuantityInvalidException(Item::findOrFail($itemId));
        }

        $lastInventory = self::getLastReference($itemId, $itemUnitId, $warehouseId, $productionNumber, $expiryDate);

        $inventory = new Inventory;
        $inventory->form_id = $formId;
        $inventory->warehouse_id = $warehouseId;
        $inventory->item_id = $itemId;
        $inventory->item_unit_id = $itemUnitId;
        $inventory->production_number = $productionNumber;
        $inventory->expiry_date = $expiryDate;
        $inventory->quantity = $quantity;
        $inventory->price = $price;
        $inventory->total_quantity = $quantity;

        // check if stock is enough to prevent stock minus
        if ($quantity < 0 && (! $lastInventory || $lastInventory->total_quantity < $quantity)) {
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
            $inventory->total_value = $inventory->total_quantity * $lastInventory->cogs;
        }

        if ($inventory->quantity > 0) {
            $inventory->cogs = $inventory->total_value / $inventory->total_quantity;
        } else {
            $inventory->cogs = $lastInventory->cogs;
        }

        $inventory->save();
    }

    public static function increase($formId, $warehouseId, $itemId, $itemUnitId, $productionNumber, $expiryDate, $quantity, $price)
    {
        Item::where('id', $itemId)->increment('stock', $quantity);

        $detail = ItemDetail::where('item_id', $itemId)->where('production_number', $productionNumber)->where('expiry_date', $expiryDate);

        if ($detail->count()) {
            $detail->increment('stock', $quantity);
        } else {
            $itemDetail = new ItemDetail;
            $itemDetail->item_id = $itemId;
            $itemDetail->stock = abs($quantity);
            $itemDetail->production_number = $productionNumber;
            $itemDetail->expiry_date = $expiryDate;
            $itemDetail->save();
        }

        self::insert($formId, $warehouseId, $itemId, $itemUnitId, $productionNumber, $expiryDate, abs($quantity), $price);
    }

    public static function decrease($formId, $warehouseId, $itemId, $itemUnitId, $productionNumber, $expiryDate, $quantity)
    {
        Item::where('id', $itemId)->decrement('stock', $quantity);

        $detail = ItemDetail::where('item_id', $itemId)->where('production_number', $productionNumber)->where('expiry_date', $expiryDate);

        if ($detail->count()) {
            $detail->decrement('stock', $quantity);
        } else {
            $itemDetail = new ItemDetail;
            $itemDetail->item_id = $itemId;
            $itemDetail->stock = -abs($quantity);
            $itemDetail->production_number = $productionNumber;
            $itemDetail->expiry_date = $expiryDate;
            $itemDetail->save();
        }

        self::insert($formId, $warehouseId, $itemId, $itemUnitId, $productionNumber, $expiryDate, -abs($quantity), 0);
    }

    /**
     * @param $formId
     * @param $warehouseId
     * @param $itemId
     * @param $quantity
     * @param $price
     * @throws ItemQuantityInvalidException
     */
    public static function audit($formId, $warehouseId, $itemId, $itemUnitId, $productionNumber, $expiryDate, $quantity, $price)
    {
        $item = Item::where('id', $itemId)->first();

        $lastInventory = self::getLastReference($itemId, $itemUnitId, $warehouseId, $productionNumber, $expiryDate);

        if (! $lastInventory && ! $price) {
            throw new ItemQuantityInvalidException($item);
        }

        $item->stock = $quantity;
        $item->save();

        $cogs = $price ?? $lastInventory->cogs;

        $inventory = new Inventory;
        $inventory->form_id = $formId;
        $inventory->warehouse_id = $warehouseId;
        $inventory->item_id = $itemId;
        $inventory->item_unit_id = $itemUnitId;
        $inventory->production_number = $productionNumber;
        $inventory->expiry_date = $expiryDate;
        $inventory->quantity = $quantity;
        $inventory->price = 0;
        $inventory->cogs = $cogs;
        $inventory->total_quantity = $quantity;
        $inventory->total_value = $quantity * $cogs;
        $inventory->is_audit = true;
        $inventory->save();
    }

    /**
     * Get last reference from inventory
     * Usually we will used it for get last stock or value of some item in warehouse.
     *
     * @param $itemId
     * @param $warehouseId
     * @return mixed
     */
    private static function getLastReference($itemId, $itemUnitId, $warehouseId, $productionNumber, $expiryDate)
    {
        return Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('item_unit_id', $itemUnitId)
            ->where('warehouse_id', $warehouseId)
            ->where('production_number', $productionNumber)
            ->where('expiry_date', $expiryDate)
            ->orderBy('date', 'DESC')
            ->orderBy('form_id', 'DESC')
            ->first();
    }
}
