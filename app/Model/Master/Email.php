<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Email extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning emailable models.
     */
    public function emailable()
    {
        return $this->morphTo();
    }
}
