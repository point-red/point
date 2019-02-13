<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeReligion extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employees for the religion.
     */
    public function employees()
    {
        return $this->hasMany(get_class(new Employee()), 'employee_religion_id');
    }
}
