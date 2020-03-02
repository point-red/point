<?php

namespace App\Model\Inventory\InventoryUsage;

use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class InventoryUsage extends TransactionModel
{
    public static $morphName = 'InventoryUsage';

    protected $connection = 'tenant';

    public $timestamps = false;

    public $defaultNumberPrefix = 'IU';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(InventoryUsageItem::class);
    }
}
