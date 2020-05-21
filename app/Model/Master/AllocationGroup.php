<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\AllocationGroupJoin;
use App\Traits\Model\Master\AllocationGroupRelation;

class AllocationGroup extends MasterModel
{
    use AllocationGroupJoin, AllocationGroupRelation;

    protected $connection = 'tenant';

    protected $fillable = ['name'];

    public static $alias = 'allocation_group';
}
