<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class ContactPerson extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning contactable models.
     */
    public function contactable()
    {
        return $this->morphTo();
    }
}
