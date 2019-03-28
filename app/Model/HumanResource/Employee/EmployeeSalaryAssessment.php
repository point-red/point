<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;
use App\Model\HumanResource\Employee\EmployeeSalary;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessmentScore;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessmentTarget;

class EmployeeSalaryAssessment extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the salary that owns the assessment.
     */
    public function salary()
    {
        return $this->belongsTo(get_class(new EmployeeSalary()));
    }

    /**
     * Get the scores for the assessment.
     */
    public function scores()
    {
        return $this->hasMany(get_class(new EmployeeSalaryAssessmentScore()), 'assessment_id');
    }

    /**
     * Get the scores for the assessment.
     */
    public function targets()
    {
        return $this->hasMany(get_class(new EmployeeSalaryAssessmentTarget()), 'assessment_id');
    }
}
