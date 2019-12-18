<?php

namespace App\Model\Manufacture\ManufactureFormula;

use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class ManufactureFormulaRawMaterial extends TransactionModel
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
    ];

    protected $casts = [
        'quantity' => 'double',
    ];

    public function manufactureFormula()
    {
        return $this->belongsTo(ManufactureFormula::class);
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
