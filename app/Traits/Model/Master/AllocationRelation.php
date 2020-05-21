<?php

namespace App\Traits\Model\Master;

use App\Model\AllocationReport;
use App\Model\Master\AllocationGroup;

trait AllocationRelation
{
    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->belongsToMany(AllocationGroup::class);
    }

    public function reports()
    {
        return $this->hasMany(AllocationReport::class);
    }
}
