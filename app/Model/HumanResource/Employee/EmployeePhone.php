<?php

namespace App\Model\HumanResource\Employee\Employee;

use App\Model\HumanResource\Employee\Employee;
use App\Model\MasterModel;

class EmployeePhone extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employee that owns the phone.
     */
    public function employee()
    {
        return $this->belongsTo(get_class(new Employee()), 'employee_id');
    }
}
