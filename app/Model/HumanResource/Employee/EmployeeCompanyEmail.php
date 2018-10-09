<?php

namespace App\Model\HumanResource\Employee\Employee;

use App\Model\HumanResource\Employee\Employee;
use Illuminate\Database\Eloquent\Model;

class EmployeeCompanyEmail extends Model
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
