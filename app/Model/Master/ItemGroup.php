<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\ItemGroupJoin;
use App\Traits\Model\Master\ItemGroupRelation;

class ItemGroup extends MasterModel
{
    use ItemGroupJoin, ItemGroupRelation;

    protected $connection = 'tenant';

    protected $fillable = ['name', 'type'];

    public static $alias = 'item_group';
}
