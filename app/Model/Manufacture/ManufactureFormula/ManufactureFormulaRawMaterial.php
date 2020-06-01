<?php

namespace App\Model\Manufacture\ManufactureFormula;

use App\Model\TransactionModel;
use App\Traits\Model\Manufacture\ManufactureFormulaRawMaterialJoin;
use App\Traits\Model\Manufacture\ManufactureFormulaRawMaterialRelation;

class ManufactureFormulaRawMaterial extends TransactionModel
{
    use ManufactureFormulaRawMaterialJoin, ManufactureFormulaRawMaterialRelation;

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
}
