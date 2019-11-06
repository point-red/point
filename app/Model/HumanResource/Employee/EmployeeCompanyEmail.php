<?php

namespace App\Model\HumanResource\Employee\Employee;

use App\Model\HumanResource\Employee\Employee;
use App\Model\MasterModel;

class EmployeeCompanyEmail extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employee that owns the email.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
