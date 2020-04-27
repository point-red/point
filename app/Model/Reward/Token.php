<?php

namespace App\Model\Reward;

use App\Model\PointModel;

class Token extends PointModel
{
    protected $connection = 'mysql';

    public static $alias = 'token';

    protected $fillable = [
        'user_id',
        'source',
        'amount',
    ];
}
