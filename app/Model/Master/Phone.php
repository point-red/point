<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Phone extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning phoneable models.
     */
    public function phoneable()
    {
        return $this->morphTo();
    }
}
