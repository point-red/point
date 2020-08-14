<?php

namespace App\Traits\Model\Master;

use App\Model\Master\ItemUnit;
use App\Model\Master\PricingGroup;

trait PriceListItemRelation
{
    public function itemUnit()
    {
        return $this->belongsTo(ItemUnit::class);
    }

    public function pricingGroups()
    {
        return $this->belongsTo(PricingGroup::class);
    }
}
