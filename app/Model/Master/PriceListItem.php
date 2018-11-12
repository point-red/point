<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class PriceListItem extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'pricing_group_id',
        'item_unit_id',
        'date',
        'price',
        'discount_percent',
        'discount_value',
        'notes',
    ];

    public function itemUnit() {
        return $this->belongsTo(ItemUnit::class);
    }
}
