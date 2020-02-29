<?php

namespace App\Model\Inventory\TransferItem;

use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class ReceiveItem extends TransactionModel
{
    public static $morphName = 'ReceiveItem';

    protected $connection = 'tenant';

    public $timestamps = false;

    public $defaultNumberPrefix = 'TIS';

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
        return $this->hasMany(ReceiveItemItem::class);
    }
}
