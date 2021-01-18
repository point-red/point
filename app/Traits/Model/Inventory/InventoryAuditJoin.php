<?php

namespace App\Traits\Model\Inventory;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Inventory\InventoryAudit\InventoryAudit;
use App\Model\Inventory\InventoryAudit\InventoryAuditItem;

trait InventoryAuditJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', InventoryAudit::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', InventoryAudit::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(InventoryAuditItem::getTableName().' as '.InventoryAuditItem::$alias,
                InventoryAuditItem::$alias.'.purchase_invoice_id', '=', InventoryAudit::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as '.Item::$alias,
                    Item::$alias.'.id', '=', InventoryAuditItem::$alias.'.item_id');
            }
        }

        return $query;
    }
}
