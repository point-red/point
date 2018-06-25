<?php

namespace App\Model\HumanResource\Employee;

use App\Model\Master\Person;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $connection = 'tenant';

    public function person()
    {
        return $this->belongsTo(get_class(new Person()));
    }
}
