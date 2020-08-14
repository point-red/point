<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;

trait SalesDownPaymentJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('customer', $joins)) {
            $query = $query->join(Customer::getTableName().' as '.Customer::$alias, function ($q) {
                $q->on(SalesDownPayment::$alias.'.customer_id', '=', Customer::$alias.'.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', SalesDownPayment::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', SalesDownPayment::$morphName);
            });
        }

        return $query;
    }
}
