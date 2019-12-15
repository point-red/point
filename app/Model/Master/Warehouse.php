<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Warehouse extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
    ];
}
