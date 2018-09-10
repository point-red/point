<?php

namespace App\Model\Plugin\ScaleWeight;

use Illuminate\Database\Eloquent\Model;

class ScaleWeightTruck extends Model
{
    protected $connection = 'tenant';

    protected $table = 'scale_weight_trucks';
}
