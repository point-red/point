<?php

namespace App\Model\Inventory\TransferItem;

use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class TransferItem extends TransactionModel
{
    public static $morphName = 'TransferItem';

    protected $connection = 'tenant';

    public static $alias = 'transfer_sent';

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
        return $this->hasMany(TransferItemItem::class);
    }
}
