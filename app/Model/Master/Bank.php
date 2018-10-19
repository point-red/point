<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Bank extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning bankable models.
     */
    public function bankable()
    {
        return $this->morphTo();
    }
}
