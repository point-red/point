<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeSocialMedia extends Model
{
    protected $connection = 'tenant';

    public function employee()
    {
        return $this->belongsTo(get_class(new Employee()), 'employee_id');
    }
}
