<?php

namespace App\Model\Reward;

use App\Model\PointModel;

class Point extends PointModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'user_id',
        'amount',
    ];

    protected $appends = [
        'action_str',
    ];

    public function rewardable()
    {
        return $this->morphTo();
    }

    public function getActionStrAttribute()
    {
        return $this->rewardable->getActionName();
    }
}
