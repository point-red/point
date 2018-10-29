<?php

namespace App\Model\Plugin\PinPoint;

use Illuminate\Database\Eloquent\Model;

class SalesVisitationNotInterestReason extends Model
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitation_not_interest_reasons';
}
