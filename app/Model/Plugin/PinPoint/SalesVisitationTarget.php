<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\Master\User;
use App\Model\PointModel;

class SalesVisitationTarget extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitation_targets';

    protected $fillable = ['date', 'user_id', 'call', 'effective_call', 'value'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
