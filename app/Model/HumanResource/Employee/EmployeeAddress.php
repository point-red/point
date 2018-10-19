<?php

namespace App\Model\HumanResource\Employee\Employee;

use App\Model\HumanResource\Employee\Employee;
use App\Model\MasterModel;

class EmployeeAddress extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employee that owns the address.
     */
    public function employee()
    {
        return $this->belongsTo(get_class(new Employee()), 'employee_id');
    }
}
