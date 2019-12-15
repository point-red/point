<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeGroup extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employees for the group.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'employee_id');
    }
}
