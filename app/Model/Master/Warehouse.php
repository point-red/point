<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\WarehouseJoin;
use App\Traits\Model\Master\WarehouseRelation;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $address
 * @property string $phone
 * @property string $notes
 * @property int $branch_id
 * @property int $created_by
 * @property int $updated_by
 * @property null|int $archived_by
 * @property string $created_at
 * @property string $updated_at
 * @property null|string $archived_at
 */
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
        'notes',
    ];

    public static $alias = 'warehouse';

    public static $morphName = 'Warehouse';

    public function getLabelAttribute()
    {
        $label = $this->code ? '['.$this->code.'] ' : '';

        return $label.$this->name;
    }
}
