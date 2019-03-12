<?php

namespace App\Model\Plugin\ScaleWeight;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ScaleWeightItem extends Model
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
