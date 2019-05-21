<?php

namespace App\Model\HumanResource\Employee\Employee;

use App\Model\MasterModel;
use App\Model\HumanResource\Employee\Employee;

class EmployeePhone extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employee that owns the phone.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
