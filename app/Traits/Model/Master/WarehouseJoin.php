<?php

namespace App\Traits\Model\Master;

use App\Model\Master\User;
use App\Model\Master\Warehouse;

trait WarehouseJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('user_warehouse', $joins)) {
            $query = $query->join('user_warehouse', function ($q) {
                $q->on('user_warehouse.warehouse_id', '=', Warehouse::$alias.'.id');
            });
        }

        return $query;
    }
}
