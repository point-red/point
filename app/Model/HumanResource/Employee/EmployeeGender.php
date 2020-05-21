<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeGender extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_gender';

    /**
     * Get the employees for the gender.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'employee_gender_id');
    }
}
