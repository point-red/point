<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class PriceListService extends Model
{
    protected $connection = 'tenant';

    public function service() {
        return $this->belongsTo(Service::class);
    }
}
