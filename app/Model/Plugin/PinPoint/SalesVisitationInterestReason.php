<?php

namespace App\Model\Plugin\PinPoint;

use Illuminate\Database\Eloquent\Model;

class SalesVisitationInterestReason extends Model
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitation_interest_reasons';
}
