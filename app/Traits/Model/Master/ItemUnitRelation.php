<?php

namespace App\Traits\Model\Master;

use App\Model\Master\Item;
use App\Model\Master\PriceListItem;
use App\Model\Master\PricingGroup;

trait ItemUnitRelation
{
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
