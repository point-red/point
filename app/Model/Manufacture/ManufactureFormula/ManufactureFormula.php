<?php

namespace App\Model\Manufacture\ManufactureFormula;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\TransactionModel;
use App\Traits\Model\Manufacture\ManufactureFormulaJoin;
use App\Traits\Model\Manufacture\ManufactureFormulaRelation;

class ManufactureFormula extends TransactionModel
{
    use ManufactureFormulaJoin, ManufactureFormulaRelation;

    public static $morphName = 'ManufactureFormula';

    public $timestamps = false;

    protected $connection = 'tenant';

    public static $alias = 'manufacture_formula';

    protected $fillable = [
        'manufacture_process_id',
        'manufacture_process_name',
        'name',
        'notes',
    ];

    public $defaultNumberPrefix = 'MF';

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
    }

    public static function create($data)
    {
        $formula = new self;
        $formula->fill($data);

        $rawMaterials = self::mapRawMaterials($data['raw_materials'] ?? []);
        $finishedGoods = self::mapFinishedGoods($data['finished_goods'] ?? []);

        $formula->save();

        $formula->rawMaterials()->saveMany($rawMaterials);
        $formula->finishedGoods()->saveMany($finishedGoods);

        $form = new Form;
        $form->saveData($data, $formula);

        return $formula;
    }

    private static function mapRawMaterials($rawMaterials)
    {
        return array_map(function ($rawMaterial) {
            $formulaRawMaterial = new ManufactureFormulaRawMaterial;
            $formulaRawMaterial->fill($rawMaterial);

            return $formulaRawMaterial;
        }, $rawMaterials);
    }

    private static function mapFinishedGoods($finishedGoods)
    {
        return array_map(function ($finishedGood) {
            $formulaFinishedGood = new ManufactureFormulaFinishedGood;
            $formulaFinishedGood->fill($finishedGood);

            return $formulaFinishedGood;
        }, $finishedGoods);
    }
}
