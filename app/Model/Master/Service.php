<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Service extends MasterModel
{
    public static $morphName = 'Service';

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
}
