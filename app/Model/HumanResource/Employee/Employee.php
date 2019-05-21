<?php

namespace App\Model\HumanResource\Employee;

use App\Model\Master\User;
use App\Model\MasterModel;
use App\Model\Finance\Payment\Payment;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Employee\Employee\EmployeePhone;
use App\Model\HumanResource\Employee\Employee\EmployeeAddress;
use App\Model\HumanResource\Employee\Employee\EmployeeCompanyEmail;

class Employee extends MasterModel
{
    public static $morphName = 'Employee';

    protected $connection = 'tenant';

    protected $casts = [
        'daily_transport_allowance' => 'double',
        'functional_allowance' => 'double',
        'communication_allowance' => 'double',
    ];

    /**
     * Get the group that owns the employee.
     */
    public function group()
    {
        return $this->belongsTo(EmployeeGroup::class, 'employee_group_id');
    }

    /**
     * Get the gender that owns the employee.
     */
    public function gender()
    {
        return $this->belongsTo(EmployeeGender::class, 'employee_gender_id');
    }

    /**
     * Get the religion that owns the employee.
     */
    public function religion()
    {
        return $this->belongsTo(EmployeeReligion::class, 'employee_religion_id');
    }

    /**
     * Get the gender that owns the employee.
     */
    public function maritalStatus()
    {
        return $this->belongsTo(EmployeeMaritalStatus::class, 'employee_marital_status_id');
    }

    /**
     * Get the status that owns the employee.
     */
    public function status()
    {
        return $this->belongsTo(EmployeeStatus::class, 'employee_status_id');
    }

    /**
     * Get the job location that owns the employee.
     */
    public function jobLocation()
    {
        return $this->belongsTo(EmployeeJobLocation::class, 'employee_job_location_id');
    }

    /**
     * Get the phones for the employee.
     */
    public function phones()
    {
        return $this->hasMany(EmployeePhone::class);
    }

    /**
     * Get the addresses for the employee.
     */
    public function addresses()
    {
        return $this->hasMany(EmployeeAddress::class);
    }

    /**
     * Get the emails for the employee.
     */
    public function emails()
    {
        return $this->hasMany(EmployeeEmail::class);
    }

    /**
     * Get the emails for the employee.
     */
    public function companyEmails()
    {
        return $this->hasMany(EmployeeCompanyEmail::class);
    }

    /**
     * Get the social media for the employee.
     */
    public function socialMedia()
    {
        return $this->hasMany(EmployeeSocialMedia::class);
    }

    /**
     * Get the contracts for the employee.
     */
    public function contracts()
    {
        return $this->hasMany(EmployeeContract::class);
    }

    /**
     * Get the salary histories for the employee.
     */
    public function salaryHistories()
    {
        return $this->hasMany(EmployeeSalaryHistory::class);
    }

    /**
     * Get the kpi template for employee.
     */
    public function kpiTemplate()
    {
        return $this->belongsTo(KpiTemplate::class);
    }

    /**
     * The scorers that belong to the employee.
     */
    public function scorers()
    {
        return $this->belongsToMany('App\Model\Master\User', 'employee_scorer', 'employee_id', 'user_id');
    }

    /**
     * The user that is connected to the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer's payment.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }
}
