<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\ServiceJoin;
use App\Traits\Model\Master\ServiceRelation;

class Service extends MasterModel
{
    use ServiceJoin, ServiceRelation;

    protected $connection = 'tenant';

    protected $appends = ['label'];

    protected $fillable = [
        'name',
        'code',
        'notes',
        'disabled',
    ];

    public static $alias = 'service';

    public static $morphName = 'Service';

    public function getLabelAttribute()
    {
        $label = $this->code ? '['.$this->code.'] ' : '';

        return $label.$this->name;
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
