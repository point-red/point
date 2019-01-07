<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Employee\Employee\EmployeePhone;
use App\Model\HumanResource\Employee\Employee\EmployeeAddress;
use App\Model\HumanResource\Employee\Employee\EmployeeCompanyEmail;

class Employee extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the group that owns the employee.
     */
    public function group()
    {
        return $this->belongsTo(get_class(new EmployeeGroup()), 'employee_group_id');
    }

    /**
     * Get the gender that owns the employee.
     */
    public function gender()
    {
        return $this->belongsTo(get_class(new EmployeeGender()), 'employee_gender_id');
    }

    /**
     * Get the religion that owns the employee.
     */
    public function religion()
    {
        return $this->belongsTo(get_class(new EmployeeReligion()), 'employee_religion_id');
    }

    /**
     * Get the gender that owns the employee.
     */
    public function maritalStatus()
    {
        return $this->belongsTo(get_class(new EmployeeMaritalStatus()), 'employee_marital_status_id');
    }

    /**
     * Get the phones for the employee.
     */
    public function phones()
    {
        return $this->hasMany(get_class(new EmployeePhone()));
    }

    /**
     * Get the addresses for the employee.
     */
    public function addresses()
    {
        return $this->hasMany(get_class(new EmployeeAddress()));
    }

    /**
     * Get the emails for the employee.
     */
    public function emails()
    {
        return $this->hasMany(get_class(new EmployeeEmail()));
    }

    /**
     * Get the emails for the employee.
     */
    public function companyEmails()
    {
        return $this->hasMany(get_class(new EmployeeCompanyEmail()));
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

    /**
     * The scorers that belong to the employee.
     */
    public function scorers()
    {
        return $this->belongsToMany('App\Model\Master\User', 'employee_scorer', 'employee_id', 'user_id');
    }
}
