<?php

namespace App\Traits\Model\Master;

trait ItemUnitJoin
{
    public static function joins($query, $joins) {
        $joins = explode(',', $joins);

        if (!$joins) {
            return $query;
        }

        return $query;
    }
}
