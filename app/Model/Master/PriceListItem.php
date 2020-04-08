<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class PriceListItem extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'price_list_item';

    protected $fillable = [
        'pricing_group_id',
        'item_unit_id',
        'date',
        'price',
        'discount_percent',
        'discount_value',
    ];

    protected $casts = [
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
    ];

    public function itemUnit()
    {
        return $this->belongsTo(ItemUnit::class);
    }

    public function pricingGroups()
    {
        return $this->belongsTo(PricingGroup::class);
    }
}
