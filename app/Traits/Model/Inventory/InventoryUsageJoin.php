<?php

namespace App\Traits\Model\Inventory;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Inventory\InventoryUsage\InventoryUsage;
use App\Model\Inventory\InventoryUsage\InventoryUsageItem;

trait InventoryUsageJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', InventoryUsage::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', InventoryUsage::$morphName);
            });
        }

        if (in_array('warehouse', $joins)) {
            $query = $query->join(Warehouse::getTableName().' as '.Warehouse::$alias, function ($q) {
                $q->on(InventoryUsage::$alias.'.warehouse_id', '=', Warehouse::$alias.'.id');
            });
        }

        if (in_array('employee', $joins)) {
            $query = $query->join(Employee::getTableName().' as '.Employee::$alias, function ($q) {
                $q->on(InventoryUsage::$alias.'.employee_id', '=', Employee::$alias.'.id');
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(InventoryUsageItem::getTableName().' as '.InventoryUsageItem::$alias,
                InventoryUsageItem::$alias.'.inventory_usage_id', '=', InventoryUsage::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as '.Item::$alias,
                    Item::$alias.'.id', '=', InventoryUsageItem::$alias.'.item_id');
            }
        }

        return $query;
    }
}
