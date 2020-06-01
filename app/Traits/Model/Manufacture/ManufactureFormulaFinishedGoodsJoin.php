<?php

namespace App\Traits\Model\Manufacture;

trait ManufactureFormulaFinishedGoodsJoin
{
    public static function joins($query, $joins) {
        $joins = explode(',', $joins);

        if (!$joins) {
            return $query;
        }

        return $query;
    }
}
