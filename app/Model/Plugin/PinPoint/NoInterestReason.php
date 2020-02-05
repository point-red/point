<?php

namespace App\Model\Plugin\PinPoint;

use Illuminate\Database\Eloquent\Model;

class NoInterestReason extends Model
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_no_interest_reasons';
}
