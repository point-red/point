<?php

namespace App\Traits\Model\Pos;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Pos\PosBill;

trait PosBillJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', PosBill::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', PosBill::$morphName);
            });
        }

        if (in_array('customer', $joins)) {
            $query = $query->join(Customer::getTableName().' as '.Customer::$alias, function ($q) {
                $q->on(PosBill::$alias.'.customer_id', '=', Customer::$alias.'.id');
            });
        }

        return $query;
    }
}
