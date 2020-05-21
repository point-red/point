<?php

namespace App\Traits\Model\Master;

use App\Model\Master\ServiceGroup;

trait ServiceRelation
{
    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->belongsToMany(ServiceGroup::class);
    }
}
