<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeScorer extends MasterModel
{
    protected $connection = 'tenant';

    protected $table = 'employee_scorer';
}
