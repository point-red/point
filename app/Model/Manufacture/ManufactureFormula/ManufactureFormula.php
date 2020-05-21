<?php

namespace App\Model\Manufacture\ManufactureFormula;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\Manufacture\ManufactureProcess\ManufactureProcess;
use App\Model\Manufacture\ManufactureInput\ManufactureInput;
use App\Model\TransactionModel;

class ManufactureFormula extends TransactionModel
{
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

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function rawMaterials()
    {
        return $this->hasMany(ManufactureFormulaRawMaterial::class);
    }

    public function finishedGoods()
    {
        return $this->hasMany(ManufactureFormulaFinishedGood::class);
    }

    public function manufactureProcess()
    {
        return $this->belongsTo(ManufactureProcess::class);
    }

    public function inputMaterials()
    {
        return $this->hasMany(ManufactureInput::class)->active();
    }

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
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

    private function isNotReferenced()
    {
        // Check if not referenced by input material
        if ($this->inputMaterials->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by input material', $this->inputMaterials);
        }
    }
}
