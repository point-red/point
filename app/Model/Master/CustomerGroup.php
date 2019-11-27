<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class CustomerGroup extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = ['name'];

    /**
     * get all of the customers that are assigned this group.
     */
    public function customers()
    {
        return $this->belongstomany(Customer::class);
    }
}
