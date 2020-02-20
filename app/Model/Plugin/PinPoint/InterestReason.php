<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\MasterModel;

class InterestReason extends MasterModel
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_interest_reasons';

    protected $fillable = [
        'name',
    ];
}
