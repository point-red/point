<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeSalaryAdditionalComponent extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_salary_additional_components';
}
