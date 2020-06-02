<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\Sales\SalesQuotation\SalesQuotation;
use App\Model\Sales\SalesQuotation\SalesQuotationItem;

trait SalesQuotationJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('customer', $joins)) {
            $query = $query->join(Customer::getTableName().' as '.Customer::$alias, function ($q) {
                $q->on(SalesQuotation::$alias.'.customer_id', '=', Customer::$alias.'.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', SalesQuotation::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', SalesQuotation::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(SalesQuotationItem::getTableName().' as '.SalesQuotationItem::$alias,
                SalesQuotationItem::$alias.'.sales_quotation_id', '=', SalesQuotation::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as '.Item::$alias,
                    Item::$alias.'.id', '=', SalesQuotationItem::$alias.'.item_id');
            }
        }

        return $query;
    }
}
