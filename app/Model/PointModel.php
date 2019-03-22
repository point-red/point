<?php

namespace App\Model;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PointModel extends Model
{
    use EloquentFilters;

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = convert_to_server_timezone($value);
    }

    public function getDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function getCreatedAtAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public static function getTableName($column = null)
    {
        $tableName = with(new static)->getTable();

        if (isset($column)) {
            $tableName = "$tableName.$column";
        }

        return $tableName;
    }
}
