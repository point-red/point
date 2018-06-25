<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeContract extends Model
{
    protected $connection = 'tenant';
}
