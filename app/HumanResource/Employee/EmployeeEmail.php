<?php

namespace App\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeEmail extends Model
{
    protected $connection = 'tenant';
}
