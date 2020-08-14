<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\MasterModel;

class InterestReason extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'interest_reason';

    protected $table = 'pin_point_interest_reasons';

    protected $fillable = [
        'name',
    ];
}
