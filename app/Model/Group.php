<?php

namespace App\Model;

use App\Model\Master\Customer;
use App\Model\Master\Item;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
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
