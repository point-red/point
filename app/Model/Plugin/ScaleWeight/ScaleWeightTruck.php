<?php

namespace App\Model\Plugin\ScaleWeight;

use App\Model\PointModel;
use Carbon\Carbon;

class ScaleWeightTruck extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'scale_weight_trucks';

    protected $casts = [
        'gross_weight' => 'double',
        'tare_weight' => 'double',
        'net_weight' => 'double',
    ];

//    public function setTimeInAttribute($value)
//    {
//        $this->attributes['time_in'] = Carbon::parse($value, request()->header('Timezone'))->timezone('UTC')->toDateTimeString();
//    }
//
//    public function getTimeInAttribute($value)
//    {
//        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
//    }
//
//    public function setTimeOutAttribute($value)
//    {
//        $this->attributes['time_out'] = Carbon::parse($value, request()->header('Timezone'))->timezone('UTC')->toDateTimeString();
//    }
//
//    public function getTimeOutAttribute($value)
//    {
//        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
//    }
}
