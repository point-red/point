<?php

namespace App\Model\Manufacture\ManufactureMachine;

use App\Model\MasterModel;

class ManufactureMachine extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'manufacture_machine';

    protected $appends = ['label'];

    protected $fillable = [
        'code',
        'name',
        'notes',
    ];

    public function getLabelAttribute()
    {
        $label = $this->code ? '['.$this->code.'] ' : '';

        return $label.$this->name;
    }
}
