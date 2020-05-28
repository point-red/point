<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestItem;

trait PurchaseReceiveJoin
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
                $q->on(Form::$alias . '.formable_id', '=', PurchaseReceive::$alias . '.id')
                    ->where(Form::$alias . '.formable_type', PurchaseReceive::$morphName);
            });
        }

        return $query;
    }
}
