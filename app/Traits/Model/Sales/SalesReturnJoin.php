<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\SalesReturn\SalesReturnItem;

trait SalesReturnJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('customer', $joins)) {
            $query = $query->join(Customer::getTableName().' as '.Customer::$alias, function ($q) {
                $q->on(SalesReturn::$alias.'.customer_id', '=', Customer::$alias.'.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', SalesReturn::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', SalesReturn::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(SalesReturnItem::getTableName().' as '.SalesReturnItem::$alias,
            SalesReturnItem::$alias.'.sales_return_id', '=', SalesReturn::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as '.Item::$alias,
                    Item::$alias.'.id', '=', SalesReturnItem::$alias.'.item_id');
            }
        }

        return $query;
    }
}
