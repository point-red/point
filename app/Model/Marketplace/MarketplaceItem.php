<?php

namespace App\Model\Marketplace;

use Illuminate\Database\Eloquent\Model;

class MarketplaceItem extends Model
{
    protected $connection = 'mysql';

    public function units()
    {
        return $this->hasMany(MarketplaceItemUnit::class);
    }
}
