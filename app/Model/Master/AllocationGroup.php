<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class AllocationGroup extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'allocation_group';

    protected $fillable = ['name'];

    /**
     * get all of the allocations that are assigned this group.
     */
    public function allocations()
    {
        return $this->belongstomany(Allocation::class);
    }
}
