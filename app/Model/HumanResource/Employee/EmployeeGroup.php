<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeGroup extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the employees for the group.
     */
    public function employees()
    {
        return $this->hasMany(get_class(new Employee()), 'employee_id');
    }
}
