<?php

namespace App\Model\HumanResource\Employee;

use App\Model\TransactionModel;

class EmployeeSalary extends TransactionModel
{
    protected $connection = 'tenant';

    /**
     * Get the assessments for the salary.
     */
    public function assessments()
    {
        return $this->hasMany(EmployeeSalaryAssessment::class);
    }

    /**
     * Get the achievements for the salary.
     */
    public function achievements()
    {
        return $this->hasMany(EmployeeSalaryAchievement::class);
    }

    /**
     * Get the employee that owns the salary.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
