<?php

namespace App\Model\HumanResource\Employee\Employee;

use App\Model\HumanResource\Employee\Employee;
use App\Model\MasterModel;

class EmployeePhone extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_phone';

    /**
     * Get the employee that owns the phone.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
