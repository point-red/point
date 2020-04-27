<?php

namespace App\Traits\Model\Master;

use App\Model\Master\Customer;

trait CustomerGroupRelation
{
    /**
     * get all of the customers that are assigned this group.
     */
    public function customers()
    {
        return $this->belongstomany(Customer::class);
    }
}
