<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Item extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'chart_of_account_id',
        'code',
        'barcode',
        'notes',
        'size',
        'color',
        'weight',
        'stock_reminder'
    ];

    /**
     * Get all of the groups for the items.
     */
    public function groups()
    {
        return $this->morphToMany(get_class(new Group()), 'groupable');
    }

    /**
     * Get all of the units for the items.
     */
    public function units()
    {
        return $this->hasMany(get_class(new ItemUnit()));
    }
}
