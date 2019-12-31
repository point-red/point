<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class ServiceGroup extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = ['name'];

    /**
     * get all of the services that are assigned this group.
     */
    public function services()
    {
        return $this->belongstomany(Service::class);
    }
}
