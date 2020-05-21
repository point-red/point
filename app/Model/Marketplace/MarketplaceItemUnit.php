<?php

namespace App\Model\Marketplace;

use Illuminate\Database\Eloquent\Model;

class MarketplaceItemUnit extends Model
{
    protected $connection = 'mysql';

    public function item()
    {
        return $this->belongsTo(MarketplaceItem::class);
    }
}
