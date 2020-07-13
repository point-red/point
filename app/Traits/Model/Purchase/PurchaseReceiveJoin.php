<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseReceive\PurchaseReceiveItem;

trait PurchaseReceiveJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('supplier', $joins)) {
            $query = $query->join(Supplier::getTableName().' as '.Supplier::$alias, function ($q) {
                $q->on(PurchaseReceive::$alias.'.supplier_id', '=', Supplier::$alias.'.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', PurchaseReceive::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', PurchaseReceive::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(PurchaseReceiveItem::getTableName().' as '.PurchaseReceiveItem::$alias,
                PurchaseReceiveItem::$alias.'.purchase_receive_id', '=', PurchaseReceive::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as '.Item::$alias,
                    Item::$alias.'.id', '=', PurchaseReceiveItem::$alias.'.item_id');
            }
        }

        return $query;
    }
}
