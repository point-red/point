<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\SupplierGroupJoin;
use App\Traits\Model\Master\SupplierGroupRelation;

class SupplierGroup extends MasterModel
{
    use SupplierGroupRelation, SupplierGroupJoin;

    protected $connection = 'tenant';

    protected $fillable = ['name'];

    public static $alias = 'supplier_group';
}
