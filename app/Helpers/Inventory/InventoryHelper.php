<?php

namespace App\Helpers\Inventory;

use App\Exceptions\ItemQuantityInvalidException;
use App\Exceptions\ProductionNumberNotExistException;
use App\Exceptions\StockNotEnoughException;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Item;
use Illuminate\Support\Facades\DB;

class InventoryHelper
{
    /**
     * @param $formId
     * @param $warehouseId
     * @param $itemId
     * @param $quantity
     * @param $price
     * @param array $options
     * @throws ItemQuantityInvalidException
     * @throws StockNotEnoughException
     */
    private static function insert($formId, $warehouseId, $itemId, $quantity, $price, $options = [])
    {
        $item = Item::findOrFail($itemId);

        if ($quantity == 0) {
            throw new ItemQuantityInvalidException($item);
        }

        $lastInventory = self::getLastReference($itemId, $warehouseId);

        $inventory = new Inventory;
        $inventory->form_id = $formId;
        $inventory->warehouse_id = $warehouseId;
        $inventory->item_id = $itemId;
        $inventory->quantity = $quantity;
        $inventory->price = $price;
        $inventory->total_quantity = $quantity;

        if (array_key_exists('production_number', $options)) {
            if ($item->require_production_number) {
                $inventory->production_number = $options['production_number'];
            } else {
                $inventory->production_number = null;
            }
        }

        if (array_key_exists('expiry_date', $options)) {
            if ($item->require_expiry_date) {
                $inventory->expiry_date = $options['expiry_date'];
            } else {
                $inventory->expiry_date = null;
            }
        }

        // check if stock is enough to prevent stock minus
        if ($quantity < 0 && (! $lastInventory || $lastInventory->total_quantity < $quantity)) {
            throw new StockNotEnoughException($item);
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

    public static function increase($formId, $warehouseId, $itemId, $quantity, $price, $options = [])
    {
        Item::where('id', $itemId)->increment('stock', $quantity);

        self::insert($formId, $warehouseId, $itemId, abs($quantity), $price, $options);
    }

    public static function decrease($formId, $warehouseId, $itemId, $quantity, $options = [])
    {
        Item::where('id', $itemId)->decrement('stock', $quantity);

        if (array_key_exists('production_number', $options)) {
            // Check production number exist in inventory
            $exist = Inventory::where('production_number', '=', $options['production_number'])->first();
            if (!$exist) {
                return new ProductionNumberNotExistException(Item::findOrFail($itemId), $options['production_number']);
            }
        }

        self::insert($formId, $warehouseId, $itemId, abs($quantity) * -1, 0, $options);
    }

    /**
     * Check stock availability
     *
     * @param $itemId
     * @param $warehouseId
     * @param $quantity
     * @param array $options
     * @return bool
     */
    public static function available($itemId, $warehouseId, $quantity, $options = [])
    {
        $dateFrom = date('Y-m-d H:i:s');

        if (array_key_exists('date_from', $options)) {
            $dateFrom = convert_to_server_timezone($options['date_from']);
        }

        $inventory = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select(DB::raw('sum(inventories.quantity) as totalQty'))
            ->where('forms.date', '<', $dateFrom)
            ->where('inventories.warehouse_id', '=', $warehouseId)
            ->where('inventories.item_id', '=', $itemId);

        if (array_key_exists('production_number', $options)) {
            $inventory = $inventory->where('inventories.production_number', '=', $options['production_number']);
        } else if (array_key_exists('expiry_date', $options)) {
            $inventory = $inventory->where('inventories.expiry_date', '=', $options['expiry_date']);
        }

        $inventory = $inventory->first();

        if (!$inventory) {
            return false;
        }

        if ($inventory->totalQty < $quantity) {
            return false;
        }

        return true;
    }

    /**
     * Check how much stock is available
     *
     * @param $itemId
     * @param $warehouseId
     * @param array $options
     * @return int
     */
    public static function stock($itemId, $warehouseId, $options = [])
    {
        $dateFrom = date('Y-m-d H:i:s');

        if (array_key_exists('date_from', $options)) {
            $dateFrom = convert_to_server_timezone($options['date_from']);
        }

        $inventory = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->select(DB::raw('sum(inventories.quantity) as totalQty'))
            ->where('forms.date', '<', $dateFrom)
            ->where('inventories.warehouse_id', '=', $warehouseId)
            ->where('inventories.item_id', '=', $itemId);

        if (array_key_exists('production_number', $options)) {
            $inventory = $inventory->where('inventories.production_number', '=', $options['production_number']);
        } else if (array_key_exists('expiry_date', $options)) {
            $inventory = $inventory->where('inventories.expiry_date', '=', $options['expiry_date']);
        }

        $inventory = $inventory->first();

        if (!$inventory) {
            return 0;
        }

        return 0;
    }

    /**
     * @param $formId
     * @param $warehouseId
     * @param $itemId
     * @param $quantity
     * @param $price
     * @throws ItemQuantityInvalidException
     */
    public static function audit($formId, $warehouseId, $itemId, $quantity, $price)
    {
        $item = Item::where('id', $itemId)->first();

        $lastInventory = self::getLastReference($itemId, $warehouseId);

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
    private static function getLastReference($itemId, $warehouseId)
    {
        return Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('date', 'DESC')
            ->orderBy('form_id', 'DESC')
            ->first();
    }
}
