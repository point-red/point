<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\ExpeditionJoin;
use App\Traits\Model\Master\ExpeditionRelation;

class Expedition extends MasterModel
{
    use ExpeditionRelation, ExpeditionJoin;

    public static $morphName = 'Expedition';

    public static $alias = 'expedition';

    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'name',
        'notes',
    ];
}
