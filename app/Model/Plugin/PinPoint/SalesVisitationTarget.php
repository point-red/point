<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\Master\User;
use App\Model\MasterModel;

class SalesVisitationTarget extends MasterModel
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitation_targets';

    protected $fillable = ['date', 'user_id', 'call', 'effective_call', 'value'];

    protected $casts = [
        'value' => 'dobule'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
