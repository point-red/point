<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseOrder\PurchaseOrderItem;

trait PurchaseOrderJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('supplier', $joins)) {
            $query = $query->join(Supplier::getTableName().' as '.Supplier::$alias, function ($q) {
                $q->on(PurchaseOrder::$alias.'.supplier_id', '=', Supplier::$alias.'.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', PurchaseOrder::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', PurchaseOrder::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(PurchaseOrderItem::getTableName().' as '.PurchaseOrderItem::$alias,
                PurchaseOrderItem::$alias.'.purchase_order_id', '=', PurchaseOrder::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as '.Item::$alias,
                    Item::$alias.'.id', '=', PurchaseOrderItem::$alias.'.item_id');
            }
        }

        return $query;
    }
}
