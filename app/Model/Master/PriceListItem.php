<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class PriceListItem extends Model
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
