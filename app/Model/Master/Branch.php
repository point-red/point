<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\BranchJoin;
use App\Traits\Model\Master\BranchRelation;

/**
 * @property int $id
 */
class Branch extends MasterModel
{
    use BranchJoin, BranchRelation;

    protected $connection = 'tenant';

    protected $table = 'branches';

    public static $alias = 'branch';

    public static $morphName = 'Branch';

    protected $fillable = [
        'name',
        'address',
        'phone',
    ];
}
