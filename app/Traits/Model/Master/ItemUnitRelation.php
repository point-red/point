<?php

namespace App\Traits\Model\Master;

use App\Model\Master\Item;

trait ItemUnitRelation
{
    /**
     * Get the item for this unit.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
