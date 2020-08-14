<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeTrainingHistory extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_training_history';

    /**
     * Get the employee that owns the training history.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
