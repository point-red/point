<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\ItemUnitJoin;
use App\Traits\Model\Master\ItemUnitRelation;

class ItemUnit extends MasterModel
{
    use ItemUnitRelation, ItemUnitJoin;

    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'label',
        'converter',
        'item_id',
    ];

    protected $casts = [
        'converter' => 'double',
    ];

    public static $alias = 'item_unit';
}
