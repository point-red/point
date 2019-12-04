<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Model\Inventory\Inventory;

class Warehouse extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
    ];

    /**
     * The users that belong to the warehouse.
     */
    public function users()
    {
        return $this->belongsToMany('App\Model\Master\User', 'user_warehouse', 'warehouse_id', 'user_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
