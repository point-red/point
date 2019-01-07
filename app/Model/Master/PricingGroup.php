<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class PricingGroup extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'label',
        'notes',
    ];

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
}
