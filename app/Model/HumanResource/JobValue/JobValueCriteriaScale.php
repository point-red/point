<?php

namespace App\Model\HumanResource\JobValue;

use App\Model\TransactionModel;

class JobValueCriteriaScale extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'job_value_criteria_scales';

    protected $casts = [
        'value' => 'double',
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description', 'value'
    ];

    /**
     * Get the job location that owns the area value.
     */
    public function criteria()
    {
        return $this->belongsTo(JobValueCriteria::class, 'criteria_id');
    }
}
