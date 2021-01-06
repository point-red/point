<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\DeliveryNote\DeliveryNoteItem;

trait DeliveryNoteJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('customer', $joins)) {
            $query = $query->join(Customer::getTableName().' as '.Customer::$alias, function ($q) {
                $q->on(DeliveryNote::$alias.'.customer_id', '=', Customer::$alias.'.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', DeliveryNote::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', DeliveryNote::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(DeliveryNoteItem::getTableName().' as '.DeliveryNoteItem::$alias,
                DeliveryNoteItem::$alias.'.delivery_order_id', '=', DeliveryNote::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as '.Item::$alias,
                    Item::$alias.'.id', '=', DeliveryNoteItem::$alias.'.item_id');
            }
        }

        return $query;
    }
}
