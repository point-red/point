<?php

namespace App\Traits\Model\Manufacture;

trait ManufactureFormulaRawMaterialJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        return $query;
    }
}
