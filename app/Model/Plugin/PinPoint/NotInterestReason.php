<?php

namespace App\Model\Plugin\PinPoint;

use Illuminate\Database\Eloquent\Model;

class NotInterestReason extends Model
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_not_interest_reasons';
}
