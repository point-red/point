<?php

namespace App\Traits\Model\Master;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\Journal;
use App\Model\Inventory\Inventory;
use App\Model\Master\ItemGroup;
use App\Model\Master\ItemUnit;

trait ItemRelation
{
    /**
     * Get all of the item's journals.
     */
    public function journals()
    {
        return $this->morphMany(Journal::class, 'journalable');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->belongsToMany(ItemGroup::class);
    }

    /**
     * Get all of the units for the items.
     */
    public function units()
    {
        return $this->hasMany(ItemUnit::class);
    }

    public function smallest_unit()
    {
        return $this->hasOne(ItemUnit::class)->where('converter', 1);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
