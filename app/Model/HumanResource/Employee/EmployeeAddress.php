<?php

namespace App\Model\HumanResource\Employee\Employee;

use Illuminate\Database\Eloquent\Model;
use App\Model\HumanResource\Employee\Employee;

class EmployeeAddress extends Model
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
