<?php

namespace App\Model\Manufacture\ManufactureFormula;

use App\Model\TransactionModel;
use App\Traits\Model\Manufacture\ManufactureFormulaFinishedGoodsJoin;
use App\Traits\Model\Manufacture\ManufactureFormulaFinishedGoodsRelation;

class ManufactureFormulaFinishedGood extends TransactionModel
{
    use ManufactureFormulaFinishedGoodsJoin, ManufactureFormulaFinishedGoodsRelation;

    protected $connection = 'tenant';

    public static $alias = 'manufacture_formula_finished_goods';

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
