<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;

trait PurchaseDownPaymentJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('supplier', $joins)) {
            $query = $query->join(Supplier::getTableName().' as '.Supplier::$alias, function ($q) {
                $q->on(PurchaseDownPayment::$alias.'.supplier_id', '=', Supplier::$alias.'.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', PurchaseDownPayment::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', PurchaseDownPayment::$morphName);
            });
        }

        return $query;
    }
}
