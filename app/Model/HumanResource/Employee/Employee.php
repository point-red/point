<?php

namespace App\Model\HumanResource\Employee;

use App\Model\Master\Person;
use Illuminate\Database\Eloquent\Model;
use App\Model\HumanResource\Kpi\KpiTemplate;

class Employee extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the person that owns the employee.
     */
    public function person()
    {
        return $this->belongsTo(get_class(new Person()), 'person_id');
    }

    /**
     * Get the group that owns the employee.
     */
    public function group()
    {
        return $this->belongsTo(get_class(new EmployeeGroup()), 'employee_group_id');
    }

    /**
     * Get the emails for the employee.
     */
    public function companyEmails()
    {
        return $this->hasMany(get_class(new EmployeeEmail()));
    }

    /**
     * Get the social media for the employee.
     */
    public function socialMedia()
    {
        return $this->hasMany(get_class(new EmployeeSocialMedia()));
    }

    /**
     * Get the contracts for the employee.
     */
    public function contracts()
    {
        return $this->hasMany(get_class(new EmployeeContract()));
    }

    /**
     * Get the salary histories for the employee.
     */
    public function salaryHistories()
    {
        return $this->hasMany(get_class(new EmployeeSalaryHistory()));
    }

    /**
     * Get the kpi template for employee.
     */
    public function kpiTemplate()
    {
        return $this->belongsTo(get_class(new KpiTemplate()));
    }
}
