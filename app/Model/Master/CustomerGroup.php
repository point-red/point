<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use Carbon\Carbon;

class CustomerGroup extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'customer_group';

    protected $fillable = ['name'];

    /**
     * get all of the customers that are assigned this group.
     */
    public function customers()
    {
        return $this->belongstomany(Customer::class);
    }
}
