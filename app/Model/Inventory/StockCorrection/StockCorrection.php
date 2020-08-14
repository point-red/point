<?php

namespace App\Model\Inventory\StockCorrection;

use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class StockCorrection extends TransactionModel
{
    public static $morphName = 'StockCorrection';

    protected $connection = 'tenant';

    public static $alias = 'stock_correction';

    public $timestamps = false;

    public $defaultNumberPrefix = 'SC';

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
        return $this->hasMany(StockCorrectionItem::class);
    }
}
