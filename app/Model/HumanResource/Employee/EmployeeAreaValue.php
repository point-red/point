<?php

namespace App\Model\HumanResource\Employee;

use App\Model\TransactionModel;
use App\Model\HumanResource\JobValue\JobValueAssessment;

class EmployeeAreaValue extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_area_values';

    protected $fillable = [
        'year', 'value', 'notes',
    ];

    protected $casts = [
        'value' => 'double',
    ];

    /**
     * Get the job location that owns the area value.
     */
    public function jobLocation()
    {
        return $this->belongsTo(EmployeeJobLocation::class, 'job_location_id');
    }

    public function jobValue()
    {
        return $this->hasMany(JobValueAssessment::class, 'area_value_id');
    }

    public function prevJobValue()
    {
        return $this->hasMany(JobValueAssessment::class, 'prev_area_value_id');
    }
}
