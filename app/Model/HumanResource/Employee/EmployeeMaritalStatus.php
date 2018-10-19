<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeMaritalStatus extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employees for the marital status.
     */
    public function employees()
    {
        return $this->hasMany(get_class(new Employee()), 'employee_marital_status_id');
    }
}
