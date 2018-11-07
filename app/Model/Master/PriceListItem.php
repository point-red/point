<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class PriceListItem extends Model
{
    protected $connection = 'tenant';

    public function itemUnit() {
        return $this->belongsTo(ItemUnit::class);
    }
}
