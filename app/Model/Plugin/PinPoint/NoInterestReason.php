<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\MasterModel;

class NoInterestReason extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'no_interest_reason';

    protected $table = 'pin_point_no_interest_reasons';

    protected $fillable = [
        'name',
    ];
}
