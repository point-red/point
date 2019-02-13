<?php

namespace App\Model;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PointModel extends Model
{
    use EloquentFilters;

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
