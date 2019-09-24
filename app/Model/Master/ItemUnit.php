<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class ItemUnit extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'label',
        'converter',
        'item_id',
    ];

    protected $casts = [
        'converter' => 'double',
    ];

    /**
     * Get the item for this unit.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the price for this unit.
     */
    public function prices()
    {
        return $this
            ->belongsToMany(PricingGroup::class, PriceListItem::getTableName(), 'item_unit_id', 'pricing_group_id')
            ->withPivot(['price', 'discount_value', 'discount_percent', 'date', 'pricing_group_id']);
    }
}
