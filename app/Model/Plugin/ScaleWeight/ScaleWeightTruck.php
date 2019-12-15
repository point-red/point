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

    public function setTimeInAttribute($value)
    {
        $this->attributes['time_in'] = convert_to_server_timezone($value);
    }

    public function getTimeInAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function setTimeOutAttribute($value)
    {
        $this->attributes['time_out'] = convert_to_server_timezone($value);
    }

    public function getTimeOutAttribute($value)
    {
        return convert_to_local_timezone($value);
    }
}
