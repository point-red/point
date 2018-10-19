<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Group extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the customers that are assigned this group.
     */
    public function customers()
    {
        return $this->morphedByMany(get_class(new Customer()), 'groupable');
    }

    /**
     * Get all of the items that are assigned this group.
     */
    public function items()
    {
        return $this->morphedByMany(get_class(new Item()), 'groupable');
    }
}
