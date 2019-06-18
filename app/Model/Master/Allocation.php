<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Model\AllocationReport;

class Allocation extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'code',
        'notes',
        'disabled',
    ];

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    public function reports ()
    {
        return $this->hasMany(AllocationReport::class);
    }
}
