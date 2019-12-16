<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Warehouse extends MasterModel
{
    protected $connection = 'tenant';

    protected $appends = ['label'];

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
    ];

    public function getLabelAttribute()
    {
        return $this->code . ' ' . $this->name;
    }
}
