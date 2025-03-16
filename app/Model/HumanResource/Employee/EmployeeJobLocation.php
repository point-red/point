<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeJobLocation extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_job_location';

    protected $casts = [
        'multiplier_kpi' => 'double',
    ];

    /**
     * Get the employees for the job location.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'employee_job_location_id');
    }

    public function areaValues()
    {
        return $this->hasMany(EmployeeAreaValue::class, 'job_location_id')->orderBy('year', 'asc');;
    }
}
