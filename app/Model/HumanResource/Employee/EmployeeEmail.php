<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeEmail extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_email';

    /**
     * Get the employee that owns the email.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
