<?php

namespace App\Model;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PointModel extends Model
{
    use EloquentFilters;

    public static function getTableName($column = null)
    {
        $tableName = with(new static )->getTable();

        if (isset($column)) {
            $tableName = "$tableName.$column";
        }

        return $tableName;
    }
}
