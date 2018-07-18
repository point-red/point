<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeGender extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the employees for the gender.
     */
    public function employees()
    {
        return $this->hasMany(get_class(new Employee()), 'employee_gender_id');
    }
}
