<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\FixedAssetGroupJoin;
use App\Traits\Model\Master\FixedAssetGroupRelation;

class FixedAssetGroup extends MasterModel
{
    use FixedAssetGroupJoin, FixedAssetGroupRelation;

    protected $connection = 'tenant';

    protected $appends = ['label'];

    protected $fillable = [
        'name'
    ];

    public static $alias = 'fixed_asset_groups';

    public function getLabelAttribute()
    {
        $label = $this->code ? '['.$this->code.'] ' : '';

        return $label.$this->name;
    }
}
