<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeSocialMedia extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employee that owns the social media.
     */
    public function employee()
    {
        return $this->belongsTo(get_class(new Employee()), 'employee_id');
    }
}
