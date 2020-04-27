<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\PriceListItemJoin;
use App\Traits\Model\Master\PriceListItemRelation;

class PriceListItem extends MasterModel
{
    use PriceListItemRelation, PriceListItemJoin;

    protected $connection = 'tenant';

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

    public static $alias = 'price_list_item';
}
