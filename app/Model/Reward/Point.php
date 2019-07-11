<?php

namespace App\Model\Reward;

use App\Model\PointModel;

class Point extends PointModel
{
    protected $connection = 'tenant';
    
    protected $fillable = [
        'user_id',
        'redeemed_at'
    ];

    public function rewardable()
    {
        return $this->morphTo();
    }
}
