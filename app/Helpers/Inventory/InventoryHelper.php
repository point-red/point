<?php

namespace App\Helpers\Inventory;

use App\Exceptions\ExpiryDateNotFoundException;
use App\Exceptions\InputBackDateForbiddenException;
use App\Exceptions\ItemQuantityInvalidException;
use App\Exceptions\ProductionNumberNotFoundException;
use App\Exceptions\StockNotEnoughException;
use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Inventory\InventoryAudit\InventoryAudit;
use App\Model\Inventory\InventoryAudit\InventoryAuditItem;
use App\Model\Master\Item;
use App\Model\Master\Warehouse;

class InventoryHelper
{
    /**
     * @param Form $form
     * @param Warehouse $warehouse
     * @param Item $item
     * @param $quantity
     * @param $unit
     * @param $converter
     * @param array $options
     * @throws ExpiryDateNotFoundException
     * @throws ItemQuantityInvalidException
     * @throws ProductionNumberNotFoundException
     * @throws StockNotEnoughException
     */
    private static function insert(Form $form, Warehouse $warehouse, Item $item, $quantity, $unit, $converter, $options = [
        'expiry_date' => null,
        'production_number' => null,
    ])
    {
        if ($quantity == 0) {
            throw new ItemQuantityInvalidException($item);
        }

        $inventory = new Inventory;
        $inventory->form_id = $form->id;
        $inventory->warehouse_id = $warehouse->id;
        $inventory->item_id = $item->id;
        $inventory->quantity = $quantity * $converter;
        $inventory->quantity_reference = $quantity;
        $inventory->unit_reference = $unit;
        $inventory->converter_reference = $converter;

        if ($item->require_expiry_date) {
            if (array_key_exists('expiry_date', $options)) {
                $inventory->expiry_date = $options['expiry_date'];
            } else {
                throw new ExpiryDateNotFoundException($item);
            }
        }

        if ($item->require_production_number) {
            if (array_key_exists('production_number', $options)) {
                $inventory->production_number = $options['production_number'];
            } else {
                throw new ProductionNumberNotFoundException($item);
            }
        }

        // check if stock is enough to prevent stock minus
        if ($quantity < 0) {
            $stock = self::getCurrentStock($item, $form->date, $warehouse, $options);
            if (abs($quantity) > $stock) {
                throw new StockNotEnoughException($item);
            }
        }

        $auditExists = self::auditExists($item, $form->date, $warehouse, $options);
        if ($auditExists) {
            throw new InputBackDateForbiddenException($auditExists, $item);
        }

        $inventory->save();
    }

    public static function increase(Form $form, Warehouse $warehouse, Item $item, $quantity, $unit, $converter, $options = [
        'expiry_date' => null,
        'production_number' => null,
    ])
    {
        Item::where('id', $item->id)->increment('stock', $quantity * $converter);

        self::insert($form, $warehouse, $item, abs($quantity), $unit, $converter, $options);
    }

    public static function decrease(Form $form, Warehouse $warehouse, Item $item, $quantity, $unit, $converter, $options = [
        'expiry_date' => null,
        'production_number' => null,
    ])
    {
        Item::where('id', $item->id)->decrement('stock', $quantity * $converter);

        self::insert($form, $warehouse, $item, abs($quantity) * -1, $unit, $converter, $options);
    }

    /**
     * @param $form
     * @param $warehouse
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
    public static function audit(Form $form, Warehouse $warehouse, Item $item, $quantity, $unit, $converter, $options = [
        'expiry_date' => null,
        'production_number' => null,
    ])
    {
        $stock = self::getCurrentStock($item, $form->date, $warehouse, $options);

        $diff = $quantity - $stock;

        if ($quantity > $stock) {
            self::insert($form, $warehouse, $item, abs($diff), $unit, $converter, $options);
        } elseif ($quantity < $stock) {
            self::insert($form, $warehouse, $item, abs($diff) * -1, $unit, $converter, $options);
        }
    }

    /**
     * Check how much stock is available.
     *
     * @param $item
     * @param $date
     * @param $warehouse
     * @param array $options
     * @return int
     */
    public static function getCurrentStock($item, $date, $warehouse, $options)
    {
        $inventories = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->selectRaw('inventories.*, sum(quantity) as remaining')
            ->groupBy(['item_id', 'production_number', 'expiry_date'])
            ->where('item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('forms.date', '<=', $date)
            ->having('remaining', '>', 0);

        if ($item->require_expiry_date) {
            $inventories = $inventories->where('expiry_date', convert_to_server_timezone($options['expiry_date']));
        }

        if ($item->require_production_number) {
            $inventories = $inventories->where('production_number', $options['production_number']);
        }

        if (! $inventories->first()) {
            return 0;
        }

        return $inventories->first()->remaining;
    }

    /**
     * Check if audit exists
     * Item input before audit is forbidden.
     *
     * @param $item
     * @param $date
     * @param $warehouse
     * @param $options
     * @return bool
     */
    public static function auditExists($item, $date, $warehouse, $options)
    {
        $inventoryExist = InventoryAuditItem::join(InventoryAudit::getTableName(), InventoryAudit::getTableName('id'), '=', InventoryAuditItem::getTableName('inventory_audit_id'))
            ->join(Form::getTableName(), function ($query) {
                $query->on(Form::getTableName('formable_id'), '=', InventoryAudit::getTableName('id'))
                    ->where(Form::getTableName('formable_type'), '=', InventoryAudit::class);
            })
            ->where(Form::getTableName('date'), '>=', $date)
            // check if form is not canceled
            ->where(function ($query) {
                $query->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', 1);
            })
            // check if form is not archived
            ->whereNotNull(Form::getTableName('number'))
            ->where('warehouse_id', $warehouse->id)
            ->where('item_id', $item->id)
            ->select(InventoryAudit::getTableName('*'))
            ->first();

        if ($inventoryExist) {
            return $inventoryExist;
        }
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
