<?php

namespace App\Traits\Model\Manufacture;

use App\Model\Manufacture\ManufactureFormula\ManufactureFormula;
use App\Model\Master\Item;

trait ManufactureFormulaRawMaterialRelation
{
    public function manufactureFormula()
    {
        return $this->belongsTo(ManufactureFormula::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
