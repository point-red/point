<?php

namespace App\Traits\Model\Master;

use App\Model\Master\Supplier;

trait SupplierGroupRelation
{
    /**
     * Get all of the customers that are assigned this group.
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }
}
