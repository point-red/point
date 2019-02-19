<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Item extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'name',
        'chart_of_account_id',
        'barcode',
        'notes',
        'size',
        'color',
        'weight',
        'stock_reminder',
        'disabled',
    ];

    protected $casts = [
        'stock' => 'double',
        'stock_reminder' => 'double',
        'cogs' => 'double',
    ];

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    /**
     * Get all of the units for the items.
     */
    public function units()
    {
        return $this->hasMany(ItemUnit::class);
    }
}
