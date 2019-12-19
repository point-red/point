<?php

namespace App\Model\Manufacture\ManufactureMachine;

use App\Model\PointModel;
use App\Model\Manufacture\ManufactureInputMaterial\ManufactureInputMaterial;
use App\Model\Manufacture\ManufactureOutputProduct\ManufactureOutputProduct;

class ManufactureMachine extends PointModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'name',
        'notes'
    ];

    public function inputMaterials()
    {
        return $this->hasMany(ManufactureInputMaterial::class)->active();
    }

    public function outputProducts()
    {
        return $this->hasMany(ManufactureOutputProduct::class)->active();
    }

    public function isAllowedToDelete()
    {
        $this->isNotReferenced();
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
