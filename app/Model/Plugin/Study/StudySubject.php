<?php

namespace App\Model\Plugin\Study;

use Illuminate\Database\Eloquent\Model;

class StudySubject extends Model
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
