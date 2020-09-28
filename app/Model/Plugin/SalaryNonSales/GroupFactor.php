<?php

namespace App\Model\Plugin\SalaryNonSales;

use Illuminate\Database\Eloquent\Model;

class GroupFactor extends Model
{
    protected $connection = 'tenant';

    protected $table = 'jobvalue_group_factors';

    protected $fillable = [
        'name',
        'group_id'
    ];

    public function criterias()
    {
        return $this->hasMany(FactorCriteria::class, 'factorId');
    }
}
