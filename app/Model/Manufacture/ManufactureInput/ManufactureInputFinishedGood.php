<?php

namespace App\Model\Manufacture\ManufactureInput;

use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class ManufactureInputFinishedGood extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'item_name',
        'warehouse_name',
        'quantity',
        'unit',
        'converter',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
    ];

    public function manufactureInput()
    {
        return $this->belongsTo(ManufactureInput::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
