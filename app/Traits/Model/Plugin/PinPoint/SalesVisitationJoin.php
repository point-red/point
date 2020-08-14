<?php

namespace App\Traits\Model\Plugin\PinPoint;

use App\Model\Master\Customer;
use App\Model\Master\User;

trait SalesVisitationJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);
        if (! $joins) {
            return $query;
        }

        if (in_array('created_by', $joins)) {
            $query = $query->join(User::getTableName().' as '.User::$alias,
                'user.id', '=', 'form.created_by');
        }

        if (in_array('customer', $joins)) {
            $query = $query->join(Customer::getTableName().' as '.Customer::$alias,
                'customer.id', '=', 'sales_visitation.customer_id');
        }

        return $query;
    }
}
