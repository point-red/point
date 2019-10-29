<?php

namespace App\Model\Plugin\ScaleWeight;

use App\Model\PointModel;
use Carbon\Carbon;

class ScaleWeightItem extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'scale_weight_items';

//    public function setTimeAttribute($value)
//    {
//        $this->attributes['time'] = Carbon::parse($value, request()->header('Timezone'))->timezone('UTC')->toDateTimeString();
//    }
//
//    public function getTimeAttribute($value)
//    {
//        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
//    }
}
