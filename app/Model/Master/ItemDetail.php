<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class ItemDetail extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'item_id',
        'production_number',
        'expiry_date',
    ];

    protected $casts = [
        'stock' => 'double',
    ];

    /**
     * Get the item for this unit.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
