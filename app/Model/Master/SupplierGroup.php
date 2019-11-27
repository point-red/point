<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class SupplierGroup extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = ['name'];

    /**
     * Get all of the customers that are assigned this group.
     */
    public function customers()
    {
        return $this->belongsToMany(Supplier::class);
    }
}
