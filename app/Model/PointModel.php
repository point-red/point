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
        $this->attributes['date'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
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
