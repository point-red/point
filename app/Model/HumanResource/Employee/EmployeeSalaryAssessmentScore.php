<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;
use App\Model\HumanResource\Employee\EmployeeSalary;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessment;

class EmployeeSalaryAssessmentScore extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the salary assessment that owns the score.
     */
    public function assessment()
    {
        return $this->belongsTo(get_class(new EmployeeSalaryAssessment()), 'assessment_id');
    }
}
