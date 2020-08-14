<?php

namespace App\Traits\Model\Master;

use App\Model\Inventory\Inventory;
use App\Model\Master\Branch;
use App\Model\Master\User;

trait WarehouseRelation
{
    /**
     * The users that belong to the warehouse.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_warehouse')->withPivot(['is_default']);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'warehouse_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
