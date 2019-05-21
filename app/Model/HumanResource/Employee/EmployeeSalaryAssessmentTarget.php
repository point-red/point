<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;
use App\Model\HumanResource\Employee\EmployeeSalary;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessment;

class EmployeeSalaryAssessmentTarget extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the salary assessment that owns the target.
     */
    public function assessment()
    {
        return $this->belongsTo(EmployeeSalaryAssessment::class, 'assessment_id');
    }
}
