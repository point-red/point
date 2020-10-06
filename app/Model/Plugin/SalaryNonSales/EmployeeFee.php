<?php

namespace App\Model\Plugin\SalaryNonSales;

use App\Model\HumanResource\Employee\Employee;
use Illuminate\Database\Eloquent\Model;

class EmployeeFee extends Model
{
    protected $connection = 'tenant';

    protected $table = 'employee_fee';

    protected $fillable = [
        'employee_id',
        'fee',
        'score',
        'start_period',
        'end_period',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function factors()
    {
        return $this->belongsToMany(GroupFactor::class, 'employee_fee_criteria', 'employee_fee_id', 'factor_id')->withPivot('score', 'criteria_id');
    }
}
