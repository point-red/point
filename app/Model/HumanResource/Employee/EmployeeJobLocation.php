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
        return $this->hasMany(get_class(new Employee()), 'employee_job_location_id');
    }
}
