<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeStatus extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employees for the status.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'employee_status_id');
    }
}
