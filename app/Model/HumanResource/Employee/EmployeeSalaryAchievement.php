<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryAchievement extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the salary that owns the achievement.
     */
    public function salary()
    {
        return $this->belongsTo(EmployeeSalary::class);
    }
}
