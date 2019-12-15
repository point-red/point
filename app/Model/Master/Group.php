<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Group extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = ['name', 'code', 'type', 'class_reference'];

    /**
     * Get all of the suppliers that are assigned this group.
     */
    public function suppliers()
    {
        return $this->morphedByMany(Supplier::class, 'groupable');
    }

    /**
     * Get all of the customers that are assigned this group.
     */
    public function customers()
    {
        return $this->morphedByMany(Customer::class, 'groupable');
    }

    /**
     * Get all of the items that are assigned this group.
     */
    public function items()
    {
        return $this->morphedByMany(Item::class, 'groupable');
    }

    /**
     * Get all of the services that are assigned this group.
     */
    public function services()
    {
        return $this->morphedByMany(Service::class, 'groupable');
    }

    /**
     * Get all of the allocations that are assigned this group.
     */
    public function allocations()
    {
        return $this->morphedByMany(Allocation::class, 'groupable');
    }
}
