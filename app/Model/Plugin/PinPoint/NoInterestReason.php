<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\MasterModel;

class NoInterestReason extends MasterModel
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_no_interest_reasons';

    protected $fillable = [
        'name',
    ];
}
