<?php

namespace App\Traits\Model\Master;

use App\Model\Master\Allocation;

trait AllocationGroupRelation
{
    /**
     * get all of the allocations that are assigned this group.
     */
    public function allocations()
    {
        return $this->belongstomany(Allocation::class);
    }
}
