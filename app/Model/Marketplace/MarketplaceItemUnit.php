<?php

namespace App\Model\Marketplace;

use App\Model\Marketplace\MarketplaceItem;

use Illuminate\Database\Eloquent\Model;

class MarketplaceItemUnit extends Model
{
    protected $connection = 'mysql';
    protected $table = 'marketplace_item_units';

    protected $fillable = [
        'marketplace_item_id',
        'item_unit_id',
        'label',
        'name',
        'converter',
        'price',
        'discount_percent',
        'discount_value',
        'disabled',
    ];

    public function item()
    {
        return $this->belongsTo(MarketplaceItem::class);
    }
}
