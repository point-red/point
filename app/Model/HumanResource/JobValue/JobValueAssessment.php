<?php

namespace App\Model\HumanResource\JobValue;

use App\Model\MasterModel;
use App\Model\HumanResource\Employee\Employee;

class JobValueAssessment extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'job_value_assessments';

    protected $casts = [
        'total_value' => 'double',
        'total_score' => 'double'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'period_from', 'period_to', 'total_value', 'total_score'
    ];

    public function scores()
    {
        return $this->hasMany(JobValueAssessmentScore::class);
    }

    /**
     * Get the employee that owns the assessment.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}