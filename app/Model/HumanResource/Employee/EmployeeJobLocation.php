<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeJobLocation extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employees for the job location.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'employee_job_location_id');
    }
}
