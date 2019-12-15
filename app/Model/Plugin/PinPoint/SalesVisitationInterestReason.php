<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\PointModel;

class SalesVisitationInterestReason extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitation_interest_reasons';

    public $timestamps = false;

    public function salesVisitation()
    {
        return $this->belongsTo(SalesVisitation::class, 'sales_visitation_id');
    }
}
