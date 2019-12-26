<?php

namespace App\Model\Manufacture\ManufactureMachine;

use App\Model\MasterModel;

class ManufactureMachine extends MasterModel
{
    protected $connection = 'tenant';

    protected $appends = ['label'];

    protected $fillable = [
        'code',
        'name',
        'notes'
    ];

    public function getLabelAttribute()
    {
        $label = '';
        if ($this->code) {
            $label = $this->code . ' - ';
        }
        return $label . $this->name;
    }
}
