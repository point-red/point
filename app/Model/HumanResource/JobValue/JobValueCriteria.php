<?php

namespace App\Model\HumanResource\JobValue;

use App\Model\MasterModel;

class JobValueCriteria extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'job_value_criterias';

    protected $casts = [
        'total_score' => 'double',
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category', 'criteria_factor', 'total_score'
    ];

    public function scales()
    {
        return $this->hasMany(JobValueCriteriaScale::class, 'criteria_id')->orderBy('value', 'asc');
    }
}
