<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryAssessmentScore extends Model
{
    protected $connection = 'tenant';

    public static $alias = 'employee_assessment_score';

    /**
     * Get the salary assessment that owns the score.
     */
    public function assessment()
    {
        return $this->belongsTo(EmployeeSalaryAssessment::class, 'assessment_id');
    }
}
