<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryAssessment extends Model
{
    protected $connection = 'tenant';

    public static $alias = 'employee_assessment';

    /**
     * Get the salary that owns the assessment.
     */
    public function salary()
    {
        return $this->belongsTo(EmployeeSalary::class);
    }

    /**
     * Get the scores for the assessment.
     */
    public function scores()
    {
        return $this->hasMany(EmployeeSalaryAssessmentScore::class, 'assessment_id');
    }

    /**
     * Get the scores for the assessment.
     */
    public function targets()
    {
        return $this->hasMany(EmployeeSalaryAssessmentTarget::class, 'assessment_id');
    }
}
