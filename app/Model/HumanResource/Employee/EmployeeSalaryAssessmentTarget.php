<?php

namespace App\Model\HumanResource\Employee;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryAssessmentTarget extends Model
{
    protected $connection = 'tenant';

    public static $alias = 'employee_assessment_target';

    /**
     * Get the salary assessment that owns the target.
     */
    public function assessment()
    {
        return $this->belongsTo(EmployeeSalaryAssessment::class, 'assessment_id');
    }
}
