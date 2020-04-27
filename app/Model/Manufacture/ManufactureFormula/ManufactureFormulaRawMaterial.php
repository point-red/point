<?php

namespace App\Model\Manufacture\ManufactureFormula;

use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class ManufactureFormulaRawMaterial extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'manufacture_formula_raw_material';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_name',
        'quantity',
        'unit',
        'converter',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
    ];

    public function manufactureFormula()
    {
        return $this->belongsTo(ManufactureFormula::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
