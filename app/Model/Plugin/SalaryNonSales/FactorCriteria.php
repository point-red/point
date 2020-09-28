<?php

namespace App\Model\Plugin\SalaryNonSales;

use Illuminate\Database\Eloquent\Model;

class FactorCriteria extends Model
{
    protected $connection = 'tenant';
    protected $table = 'jobvalue_factor_criterias';

    protected $fillable = [
        'level',
        'description',
        'score',
        'factor_id'
    ];

    public function groupFactor()
    {
        return $this->belongsTo(GroupFactor::class, 'factor_id');
    }
}
