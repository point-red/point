<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\ServiceGroupJoin;
use App\Traits\Model\Master\ServiceGroupRelation;

class ServiceGroup extends MasterModel
{
    use ServiceGroupRelation, ServiceGroupJoin;

    protected $connection = 'tenant';

    protected $fillable = ['name'];

    public static $alias = 'service_group';
}
