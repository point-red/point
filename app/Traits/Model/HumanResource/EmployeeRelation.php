<?php

namespace App\Traits\Model\HumanResource;

use App\Model\Finance\Payment\Payment;
use App\Model\HumanResource\Employee\Employee\EmployeeCompanyEmail;
use App\Model\HumanResource\Employee\EmployeeContract;
use App\Model\HumanResource\Employee\EmployeeGender;
use App\Model\HumanResource\Employee\EmployeeGroup;
use App\Model\HumanResource\Employee\EmployeeJobLocation;
use App\Model\HumanResource\Employee\EmployeeMaritalStatus;
use App\Model\HumanResource\Employee\EmployeeReligion;
use App\Model\HumanResource\Employee\EmployeeSalaryHistory;
use App\Model\HumanResource\Employee\EmployeeSocialMedia;
use App\Model\HumanResource\Employee\EmployeeStatus;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\Master\Address;
use App\Model\Master\Branch;
use App\Model\Master\Email;
use App\Model\Master\Phone;
use App\Model\Master\User;

trait EmployeeRelation
{
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
     * Get all of the supplier's address.
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get all of the supplier's phones.
     */
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /**
     * Get all of the supplier's emails.
     */
    public function emails()
    {
        return $this->morphMany(Email::class, 'emailable');
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

    /**
     * The branch that is connected to the employee.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
