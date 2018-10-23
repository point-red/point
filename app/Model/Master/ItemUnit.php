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

    /**
     * Get the item for this unit.
     */
    public function item()
    {
        return $this->belongsTo(get_class(new Item()));
    }
}
