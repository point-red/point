<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeScorer extends Model
{
    protected $connection = 'tenant';

    protected $table = 'employee_scorer';
}
