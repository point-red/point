<?php

namespace App\Model\Manufacture\ManufactureFormula;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\FormApproval;
use App\Model\Manufacture\ManufactureProcess\ManufactureProcess;
use App\Model\Manufacture\ManufactureInputMaterial\ManufactureInputMaterial;
use App\Model\Manufacture\ManufactureOutputProduct\ManufactureOutputProduct;
use App\Model\TransactionModel;
use Carbon\Carbon;

class ManufactureFormula extends TransactionModel
{
    public static $morphName = 'ManufactureFormula';

    public $timestamps = false;

    protected $connection = 'tenant';

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

    public function finishGoods()
    {
        return $this->hasMany(ManufactureFormulaFinishGood::class);
    }

    public function manufactureProcess()
    {
        return $this->belongsTo(ManufactureProcess::class);
    }

    public function approvers()
    {
        return $this->hasManyThrough(FormApproval::class, Form::class, 'formable_id', 'form_id')->where('formable_type', self::$morphName);
    }

    public function inputMaterials()
    {
        return $this->hasMany(ManufactureInputMaterial::class)->active();
    }

    public function outputProducts()
    {
        return $this->hasMany(ManufactureOutputProduct::class)->active();
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
        $finishGoods = self::mapFinishGoods($data['finish_goods'] ?? []);

        $formula->save();

        $formula->rawMaterials()->saveMany($rawMaterials);
        $formula->finishGoods()->saveMany($finishGoods);

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

    private static function mapFinishGoods($finishGoods)
    {
        return array_map(function ($finishGood) {
            $formulaFinishGood = new ManufactureFormulaFinishGood;
            $formulaFinishGood->fill($finishGood);

            return $formulaFinishGood;
        }, $finishGoods);
    }

    private function isNotReferenced()
    {
        // Check if not referenced by input material & output product
        if ($this->inputMaterials->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by input material', $this->inputMaterials);
        }

        if ($this->outputProducts->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by output product', $this->outputProducts);
        }
    }
}
