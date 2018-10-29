<?php

namespace App\Model\Plugin\PinPoint;

use Illuminate\Database\Eloquent\Model;

class InterestReason extends Model
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_interest_reason';
}
