<?php

namespace App\Traits\Model\Manufacture;

use App\Model\Form;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormulaFinishedGood;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormulaRawMaterial;
use App\Model\Manufacture\ManufactureInput\ManufactureInput;
use App\Model\Manufacture\ManufactureProcess\ManufactureProcess;

trait ManufactureFormulaRelation
{
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
}
