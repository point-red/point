<?php

namespace App\Model\HumanResource\Employee;

use App\Model\HumanResource\Employee\Employee;
use App\Model\MasterModel;

class EmployeeAddress extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_address';

    /**
     * Get the employee that owns the address.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
