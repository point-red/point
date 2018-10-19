<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeePromotionHistory extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employee that owns the promotion history.
     */
    public function employee()
    {
        return $this->belongsTo(get_class(new Employee()), 'employee_id');
    }
}
