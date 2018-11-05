<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\Master\Item;
use Illuminate\Database\Eloquent\Model;

class SalesVisitationDetail extends Model
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitation_details';

    public $timestamps = false;

    public function item() {
        return $this->belongsTo(Item::class);
    }
}
