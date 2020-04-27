<?php

namespace App\Traits\Model\Master;

use App\Model\Master\Customer;
use App\Model\Master\PriceListItem;
use App\Model\Master\PriceListService;

trait PricingGroupRelation
{
    /**
     * Get the member of pricing group.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get the item's price of pricing group.
     */
    public function priceListItem()
    {
        return $this->hasMany(PriceListItem::class);
    }

    /**
     * Get the item's price of pricing group.
     */
    public function priceListService()
    {
        return $this->hasMany(PriceListService::class);
    }
}
