<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Service extends MasterModel
{
    public static $morphName = 'Service';

    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'code',
        'notes',
        'disabled',
    ];

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    /**
     * Get the price for this service.
     */
    public function prices()
    {
        return $this
            ->belongsToMany(PricingGroup::class, PriceListService::getTableName(), 'service_id', 'pricing_group_id')
            ->withPivot(['price', 'discount_value', 'discount_percent', 'date', 'pricing_group_id']);
    }
}
