<?php

namespace App\Model\Manufacture;

use App\Model\PointModel;

class ManufactureMachine extends PointModel
{
    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'name',
        'notes'
    ];
}
