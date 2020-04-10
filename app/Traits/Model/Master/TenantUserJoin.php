<?php

namespace App\Traits\Model\Master;

trait TenantUserJoin
{
    public static function joins($query, $joins) {

        return $query;
    }
}
