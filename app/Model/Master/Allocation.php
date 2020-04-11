<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\AllocationJoin;
use App\Traits\Model\Master\AllocationRelation;

class Allocation extends MasterModel
{
    use AllocationJoin, AllocationRelation;

    protected $connection = 'tenant';

    protected $appends = ['label'];

    protected $fillable = [
        'name',
        'code',
        'notes',
        'disabled',
    ];

    public static $alias = 'allocation';

    public static $morphName = 'Allocation';

    public function getLabelAttribute()
    {
        $label = $this->code ? '[' . $this->code . '] ' : '';

        return $label . $this->name;
    }
}
