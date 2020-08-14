<?php

namespace App\Model\Manufacture\ManufactureProcess;

use App\Exceptions\IsReferencedException;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormula;
use App\Model\PointModel;

class ManufactureProcess extends PointModel
{
    protected $connection = 'tenant';

    public static $alias = 'manufacture_process';

    protected $fillable = [
        'name',
        'notes',
    ];

    public function manufactureFormulas()
    {
        return $this->hasMany(ManufactureFormula::class);
    }

    public function isAllowedToDelete()
    {
        $this->isNotReferenced();
    }

    private function isNotReferenced()
    {
        // Check if not referenced by manufacture formula
        if ($this->manufactureFormulas->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by manufacture formula', $this->manufactureFormulas);
        }
    }
}
