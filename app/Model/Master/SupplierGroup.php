<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class SupplierGroup extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'supplier_group';

    protected $fillable = ['name'];

    /**
     * Get all of the customers that are assigned this group.
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }
}
