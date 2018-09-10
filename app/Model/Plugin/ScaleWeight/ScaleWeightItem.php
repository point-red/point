<?php

namespace App\Model\Plugin\ScaleWeight;

use Illuminate\Database\Eloquent\Model;

class ScaleWeightItem extends Model
{
    protected $connection = 'tenant';

    protected $table = 'scale_weight_items';
}
