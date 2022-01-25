<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\FixedAssetEnum;
use App\Traits\Model\Master\FixedAssetJoin;
use App\Traits\Model\Master\FixedAssetRelation;

class FixedAsset extends MasterModel
{
    use FixedAssetEnum, FixedAssetJoin, FixedAssetRelation;

    protected $connection = 'tenant';

    protected $appends = ['label', 'useful_life_month'];

    protected $fillable = [
        'name',
        'code',
        'depreciation_method',
        'fixed_asset_group_id',
        'chart_of_account_id',
        'accumulation_chart_of_account_id',
        'depreciation_chart_of_account_id',
        'useful_life_year',
        'salvage_value',
    ];

    public static $alias = 'fixed_asset';

    public static $morphName = 'FixedAsset';

    public function getLabelAttribute()
    {
        $label = $this->code ? '['.$this->code.'] ' : '';

        return $label.$this->name;
    }

    public function getUsefulLifeMonthAttribute()
    {
        if (! $this->useful_life_year) {
            return null;
        }

        return $this->useful_life_year * 12;
    }
}
