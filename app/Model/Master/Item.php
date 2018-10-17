<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Item extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->morphToMany(get_class(new Group()), 'groupable');
    }
}
