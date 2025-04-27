<?php

namespace App\Model\HumanResource\JobValue;

use App\Model\TransactionModel;

class JobValueCategory extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'job_value_categories';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category'
    ];

    public function criterias()
    {
        return $this->hasMany(JobValueCriteria::class, 'category_id');
    }
}
