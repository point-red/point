<?php

namespace App\Model\HumanResource\JobValue;

use App\Model\TransactionModel;

class JobValueAssessmentScore extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'job_value_assessment_scores';

    protected $casts = [
        'score' => 'double',
        'value' => 'double'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'criteria_id', 'score', 'value', 'description', 'note'
    ];

    public function assessment()
    {
        return $this->hasMany(JobValueAssessment::class);
    }

    public function criteria()
    {
        return $this->belongsTo(JobValueCriteria::class, 'criteria_id');
    }
}