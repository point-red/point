<?php

namespace App\Model\Plugin\SalaryNonSales;

use Illuminate\Database\Eloquent\Model;

class EmployeeFeeCriteria extends Model
{
    protected $connection = 'tenant';

    protected $table = 'employee_fee_criteria';

    protected $fillable = [
        'employee_fee_id',
        'criteria_id',
        'factor_id',
        'score'
    ];
}
