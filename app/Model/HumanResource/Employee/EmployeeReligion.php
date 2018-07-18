<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeReligion extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the employees for the religion.
     */
    public function employees()
    {
        return $this->hasMany(get_class(new Employee()), 'employee_religion_id');
    }
}
