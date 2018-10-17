<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Address extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning addressable models.
     */
    public function addressable()
    {
        return $this->morphTo();
    }
}
