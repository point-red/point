<?php

namespace App\Traits\Model\Master;

use App\Model\Master\Item;

trait ItemGroupRelation
{
    /**
     * get all of the items that are assigned this group.
     */
    public function items()
    {
        return $this->belongstomany(Item::class);
    }
}
