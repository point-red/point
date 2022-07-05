<?php

namespace App\Model\Plugin\Study;

use App\Model\PointModel;

class StudySubject extends PointModel
{
    protected $connection = 'tenant';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];
}
