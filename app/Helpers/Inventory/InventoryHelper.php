<?php

namespace App\Helpers\Inventory;

use App\Exceptions\ExpiryDateNotFoundException;
use App\Exceptions\ItemQuantityInvalidException;
use App\Exceptions\ProductionNumberNotExistException;
use App\Exceptions\ProductionNumberNotFoundException;
use App\Exceptions\StockNotEnoughException;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Item;
use App\Model\Master\Warehouse;

class InventoryHelper
{
    /**
     * @param $form
     * @param $warehouseId
     * @param $item
     * @param $quantity
     * @param $unit
     * @param $converter
     * @param array $options
     * @throws ExpiryDateNotFoundException
     * @throws ItemQuantityInvalidException
     * @throws ProductionNumberNotFoundException
     * @throws StockNotEnoughException
     */
    private static function insert(Form $form, $warehouseId, Item $item, $quantity, $unit, $converter, $options = [
        'expiry_date' => null,
        'production_number' => null,
    ])
    {
        if ($quantity == 0) {
            throw new ItemQuantityInvalidException($item);
        }

        $inventory = new Inventory;
        $inventory->form_id = $form->id;
        $inventory->warehouse_id = $warehouseId;
        $inventory->item_id = $item->id;
        $inventory->quantity = $quantity * $converter;
        $inventory->quantity_reference = $quantity;
        $inventory->unit_reference = $unit;
        $inventory->converter_reference = $converter;

        if (array_key_exists('expiry_date', $options)) {
            if ($item->require_expiry_date) {
                if ($options['expiry_date']) {
                    $inventory->expiry_date = $options['expiry_date'];
                } else {
                    throw new ExpiryDateNotFoundException($item);
                }
            } else {
                $inventory->expiry_date = null;
            }
        } else {
            if ($item->require_expiry_date) {
                throw new ExpiryDateNotFoundException($item);
            }
        }

        if (array_key_exists('production_number', $options)) {
            if ($item->require_production_number) {
                if ($options['production_number']) {
                    $inventory->production_number = $options['production_number'];
                } else {
                    throw new ProductionNumberNotFoundException($item);
                }
            } else {
                $inventory->production_number = null;
            }
        } else {
            if ($item->require_production_number) {
                throw new ProductionNumberNotFoundException($item);
            }
        }

        // check if stock is enough to prevent stock minus
        if ($quantity < 0) {
            $stock = self::getCurrentStock($item, $form->date, $warehouseId, $options);

            if (abs($quantity) > $stock) {
                throw new StockNotEnoughException($item);
            }
        }

        $inventory->save();
    }

    public static function increase($form, $warehouseId, $item, $quantity, $unit, $converter, $options = [
        'expiry_date' => null,
        'production_number' => null,
    ])
    {
        Item::where('id', $item->id)->increment('stock', $quantity * $converter);

        self::insert($form, $warehouseId, $item, abs($quantity), $unit, $converter, $options);
    }

    public static function decrease($form, $warehouseId, $item, $quantity, $unit, $converter, $options = [
        'expiry_date' => null,
        'production_number' => null,
    ])
    {
        Item::where('id', $item->id)->decrement('stock', $quantity * $converter);

        if (array_key_exists('production_number', $options)) {
            // Check production number exist in inventory
            $exist = Inventory::where('production_number', '=', $options['production_number'])
                        ->where('warehouse_id', $warehouseId)
                        ->first();
            if (!$exist) {
                throw new ProductionNumberNotExistException($item, $options['production_number'], Warehouse::findOrFail($warehouseId));
            }
        }

        self::insert($form, $warehouseId, $item, abs($quantity) * -1, $unit, $converter, $options);
    }

    /**
     * @param $form
     * @param $warehouseId
     * @param $item
     * @param $quantity
     * @param $unit
     * @param $converter
     * @param array $options
     * @throws ExpiryDateNotFoundException
     * @throws ItemQuantityInvalidException
     * @throws ProductionNumberNotFoundException
     * @throws StockNotEnoughException
     */
    public static function audit($form, $warehouseId, $item, $quantity, $unit, $converter, $options = [
        'expiry_date' => null,
        'production_number' => null,
    ])
    {
        $stock = self::getCurrentStock($item, $form->date, $warehouseId, $options);

        $diff = $quantity - $stock;

        if ($quantity > $stock) {
            self::insert($form, $warehouseId, $item, abs($diff), $unit, $converter, $options);
        } else if ($quantity < $stock) {
            self::insert($form, $warehouseId, $item, abs($diff) * -1, $unit, $converter, $options);
        }
    }

    /**
     * Check how much stock is available
     *
     * @param $item
     * @param $date
     * @param $warehouseId
     * @param array $options
     * @return int
     */
    public static function getCurrentStock($item, $date, $warehouseId, $options)
    {
        $inventories = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->selectRaw('inventories.*, sum(quantity) as remaining')
            ->groupBy(['item_id', 'production_number', 'expiry_date'])
            ->where('item_id', $item->id)
            ->where('warehouse_id', $warehouseId)
            ->where('forms.date', '<', $date)
            ->having('remaining', '>', 0);

        if ($item->require_expiry_date) {
            $inventories = $inventories->where('expiry_date', convert_to_server_timezone($options['expiry_date']));
        }

        if ($item->require_production_number) {
            $inventories = $inventories->where('production_number', $options['production_number']);
        }

        if (!$inventories->first()) {
            return 0;
        }

        return $inventories->first()->remaining;
    }

    public static function posting($formId)
    {
        $inventories = Inventory::where('form_id', '=', $formId)->get();
        foreach ($inventories as $inventory) {
            $inventory->is_posted = true;
            $inventory->save();
        }
    }

    public static function delete($formId)
    {
        Inventory::where('form_id', '=', $formId)->delete();
    }
}
