<?php

namespace App\Model\HumanResource\JobValue;

use App\Model\TransactionModel;
use App\Model\HumanResource\Employee\EmployeeAreaValue;

class JobValueAssessmentCalculation extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'job_value_assessment_calculations';

    protected $casts = [
        'score_diff' => 'double',
        'score_diff_pct' => 'double',
        'prev_area_value' => 'double',
        'area_value' => 'double',
        'area_value_diff' => 'double',
        'area_value_pct' => 'double',
        'kpi_avg' => 'double',
        'current_point' => 'double',
        'next_year_point' => 'double',
        'basic_fee' => 'double',
        'prev_fee' => 'double'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'score_diff', 'score_diff_pct', 'prev_area_value', 'area_value',
        'area_value_diff', 'area_value_pct', 'kpi_avg', 'current_point',
        'next_year_point', 'basic_fee', 'prev_fee'
    ];

    public function assessment()
    {
        return $this->belongsTo(JobValueAssessment::class, 'assessment_id');
    }

    public function prevAssessment()
    {
        return $this->belongsTo(JobValueAssessment::class, 'prev_assessment_id');
    }

    public function areaValue()
    {
        return $this->belongsTo(EmployeeAreaValue::class, 'area_value_id');
    }

    public function prevAreaValue()
    {
        return $this->belongsTo(EmployeeAreaValue::class, 'prev_area_value_id');
    }
}