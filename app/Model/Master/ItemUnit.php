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
        return $this->belongsTo(get_class(new Item()));
    }

    /**
     * Get the price for this unit.
     */
    public function pricing()
    {
        return $this->hasMany(PriceListItem::class);
    }
}
