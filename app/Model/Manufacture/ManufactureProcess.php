<?php

namespace App\Model\Manufacture;

use App\Model\PointModel;

class ManufactureProcess extends PointModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'notes'
    ];
}
