<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\Master\Item;
use App\Model\PointModel;

class SalesVisitationDetail extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitation_details';

    public $timestamps = false;

    public function salesVisitation()
    {
        return $this->belongsTo(SalesVisitation::class, 'sales_visitation_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
