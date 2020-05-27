<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestItem;

trait PurchaseRequestJoin
{
    public static function joins($query, $joins) {
        $joins = explode(',', $joins);

        if (!$joins) {
            return $query;
        }

        if (in_array('supplier', $joins)) {
            $query = $query->join(Supplier::getTableName() . ' as ' . Supplier::$alias, function ($q) {
                $q->on(PurchaseRequest::$alias . '.supplier_id', '=', Supplier::$alias . '.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName() . ' as ' . Form::$alias, function ($q) {
                $q->on(Form::$alias . '.formable_id', '=', PurchaseRequest::$alias . '.id')
                    ->where(Form::$alias . '.formable_type', PurchaseRequest::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(PurchaseRequestItem::getTableName() . ' as ' . PurchaseRequestItem::$alias,
                PurchaseRequestItem::$alias . '.purchase_request_id', '=', PurchaseRequest::$alias . '.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName() . ' as ' . Item::$alias,
                    Item::$alias . '.id', '=', PurchaseRequestItem::$alias . '.item_id');
            }
        }

        return $query;
    }
}
