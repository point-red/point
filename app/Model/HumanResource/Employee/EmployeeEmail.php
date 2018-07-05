<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeEmail extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the employee that owns the email.
     */
    public function employee()
    {
        return $this->belongsTo(get_class(new Employee()), 'employee_id');
    }
}
