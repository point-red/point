<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class PriceListService extends MasterModel
{
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

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
