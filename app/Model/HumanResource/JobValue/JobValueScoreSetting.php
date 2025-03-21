<?php

namespace App\Model\HumanResource\JobValue;

use App\Model\TransactionModel;

class JobValueScoreSetting extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'job_value_score_settings';

    protected $casts = [
        'minimum_kpi' => 'double',
        'minimum_coc' => 'double',
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'minimum_kpi', 'minimum_coc'
    ];
}
