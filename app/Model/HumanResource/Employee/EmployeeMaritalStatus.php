<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeMaritalStatus extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_marital_status';

    /**
     * Get the employees for the marital status.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'employee_marital_status_id');
    }
}
