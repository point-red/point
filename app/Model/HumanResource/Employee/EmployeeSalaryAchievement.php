<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;
use App\Model\HumanResource\Employee\EmployeeSalary;

class EmployeeSalaryAchievement extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the salary that owns the achievement.
     */
    public function salary()
    {
        return $this->belongsTo(get_class(new EmployeeSalary()));
    }
}
