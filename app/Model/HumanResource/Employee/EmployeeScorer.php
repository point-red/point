<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeScorer extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_scorer';

    protected $table = 'employee_scorer';
}
