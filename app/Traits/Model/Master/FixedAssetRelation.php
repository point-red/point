<?php

namespace App\Traits\Model\Master;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\Journal;
use App\Model\Master\FixedAssetGroup;
use App\Model\Master\ItemGroup;

trait FixedAssetRelation
{
    /**
     * Get all of the item's journals.
     */
    public function journals()
    {
        return $this->morphMany(Journal::class, 'journalable');
    }

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->belongsTo(FixedAssetGroup::class, 'fixed_asset_group_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function accumulationAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'accumulation_chart_of_account_id');
    }

    public function depreciationAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'depreciation_chart_of_account_id');
    }
}
