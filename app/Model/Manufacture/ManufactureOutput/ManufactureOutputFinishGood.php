<?php

namespace App\Model\Manufacture\ManufactureOutput;

use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Manufacture\ManufactureInput\ManufactureInputFinishGood;

class ManufactureOutputFinishGood extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'input_finish_good_id',
        'item_name',
        'warehouse_name',
        'quantity',
        'production_number',
        'expiry_date',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'double',
    ];

    public function manufactureOutput()
    {
        return $this->belongsTo(ManufactureOutput::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function manufactureInputFinishGood()
    {
        return $this->belongsTo(ManufactureInputFinishGood::class);
    }
}
