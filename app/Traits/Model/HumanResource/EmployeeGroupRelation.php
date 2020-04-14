<?php

namespace App\Traits\Model\HumanResource;

use App\Model\HumanResource\Employee\Employee;

trait EmployeeGroupRelation
{
    /**
     * Get the employees for the group.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'employee_id');
    }
}
