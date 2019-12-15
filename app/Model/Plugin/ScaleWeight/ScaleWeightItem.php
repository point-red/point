<?php

namespace App\Model\Plugin\ScaleWeight;

use App\Model\PointModel;
use Carbon\Carbon;

class ScaleWeightItem extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'scale_weight_items';

    public function setTimeAttribute($value)
    {
        $this->attributes['time'] = convert_to_server_timezone($value);
    }

    public function getTimeAttribute($value)
    {
        return convert_to_local_timezone($value);
    }
}
