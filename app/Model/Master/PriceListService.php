<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\PriceListServiceJoin;
use App\Traits\Model\Master\PriceListServiceRelation;

class PriceListService extends MasterModel
{
    use PriceListServiceRelation, PriceListServiceJoin;

    protected $connection = 'tenant';

    protected $fillable = [
        'pricing_group_id',
        'service_id',
        'date',
        'price',
        'discount_percent',
        'discount_value',
        'notes',
    ];

    protected $casts = [
        'price'            => 'double',
        'discount_percent' => 'double',
        'discount_value'   => 'double',
    ];

    public static $alias = 'price_list_service';
}
