<?php

namespace App\Model;

use Illuminate\Support\Carbon;
use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PointModel extends Model
{
    use EloquentFilters;

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value, 'Asia/Jakarta')->timezone('UTC')->toDateTimeString();
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone('Asia/Jakarta')->toDateTimeString();
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
