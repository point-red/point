<?php

namespace App\Model\Master;

use App\Model\AllocationReport;
use App\Model\MasterModel;

class Allocation extends MasterModel
{
    protected $connection = 'tenant';

    protected $appends = ['label'];

    protected $fillable = [
        'name',
        'code',
        'notes',
        'disabled',
    ];

    public function getLabelAttribute()
    {
        $label = $this->code ? '[' . $this->number . '] ' : '';

        return $label . $this->name;
    }

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    public function reports()
    {
        return $this->hasMany(AllocationReport::class);
    }
}
