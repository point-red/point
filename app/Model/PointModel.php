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
        $this->attributes['date'] = Carbon::parse($value, request()->header('Timezone'))->timezone('UTC')->toDateTimeString();
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
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
