<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\Sales\SalesOrder\SalesOrderItem;

trait SalesOrderJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('customer', $joins)) {
            $query = $query->join(Customer::getTableName().' as '.Customer::$alias, function ($q) {
                $q->on(SalesOrder::$alias.'.customer_id', '=', Customer::$alias.'.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', SalesOrder::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', SalesOrder::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(SalesOrderItem::getTableName().' as '.SalesOrderItem::$alias,
                SalesOrderItem::$alias.'.sales_order_id', '=', SalesOrder::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as '.Item::$alias,
                    Item::$alias.'.id', '=', SalesOrderItem::$alias.'.item_id');
            }
        }

        return $query;
    }
}
