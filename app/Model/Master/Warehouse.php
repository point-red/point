<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\WarehouseJoin;
use App\Traits\Model\Master\WarehouseRelation;

class Warehouse extends MasterModel
{
    use WarehouseJoin, WarehouseRelation;

    protected $connection = 'tenant';

    protected $appends = ['label'];

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'address',
        'phone',
    ];

    public static $alias = 'warehouse';

    public static $morphName = 'Warehouse';

    public function getLabelAttribute()
    {
        $label = $this->code ? '[' . $this->code . '] ' : '';

        return $label . $this->name;
    }
}
