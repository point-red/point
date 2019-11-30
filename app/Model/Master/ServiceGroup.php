<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class ServiceGroup extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = ['name'];

    /**
     * get all of the items that are assigned this group.
     */
    public function items()
    {
        return $this->belongstomany(Service::class);
    }
}
