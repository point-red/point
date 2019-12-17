<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Warehouse extends MasterModel
{
    protected $connection = 'tenant';

    protected $appends = ['label'];

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
    ];

    public function getLabelAttribute()
    {
        return $this->code . ' ' . $this->name;
    }

    /**
     * The users that belong to the warehouse.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_warehouse', 'warehouse_id', 'user_id');
    }
}
