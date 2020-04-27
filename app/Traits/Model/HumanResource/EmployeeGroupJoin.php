<?php

namespace App\Traits\Model\HumanResource;

trait EmployeeGroupJoin
{
    public static function joins($query, $joins) {
        $joins = explode(',', $joins);

        if (!$joins) {
            return $query;
        }

        return $query;
    }
}
