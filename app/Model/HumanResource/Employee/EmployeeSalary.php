<?php

namespace App\Model\HumanResource\Employee;

use App\Model\TransactionModel;
use Illuminate\Database\Eloquent\Model;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessment;
use App\Model\HumanResource\Employee\EmployeeSalaryAchievement;

class EmployeeSalary extends TransactionModel
{
    protected $connection = 'tenant';

    /**
     * Get the assessments for the salary.
     */
    public function assessments()
    {
        return $this->hasMany(get_class(new EmployeeSalaryAssessment()));
    }

    /**
     * Get the achievements for the salary.
     */
    public function achievements()
    {
        return $this->hasMany(get_class(new EmployeeSalaryAchievement()));
    }

    /**
     * Get the employee that owns the salary.
     */
    public function employee()
    {
        return $this->belongsTo(get_class(new Employee()));
    }
}
