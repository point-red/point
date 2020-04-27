<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\CustomerGroupJoin;
use App\Traits\Model\Master\CustomerGroupRelation;
use Carbon\Carbon;

class CustomerGroup extends MasterModel
{
    use CustomerGroupJoin, CustomerGroupRelation;

    protected $connection = 'tenant';

    protected $fillable = ['name'];

    public static $alias = 'customer_group';

}
