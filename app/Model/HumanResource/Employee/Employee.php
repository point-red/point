<?php

namespace App\Model\HumanResource\Employee;

use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Kpi\KpiTemplateEmployee;
use App\Model\Master\Person;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $connection = 'tenant';

    public function person()
    {
        return $this->belongsTo(get_class(new Person()), 'person_id');
    }

    public function group()
    {
        return $this->belongsTo(get_class(new EmployeeGroup()), 'employee_group_id');
    }

    public function companyEmails()
    {
        return $this->hasMany(get_class(new EmployeeEmail()));
    }

    public function socialMedia()
    {
        return $this->hasMany(get_class(new EmployeeSocialMedia()));
    }

    public function contracts()
    {
        return $this->hasMany(get_class(new EmployeeContract()));
    }

    public function salaryHistories()
    {
        return $this->hasMany(get_class(new EmployeeSalaryHistory()));
    }

    public function kpiTemplate()
    {
        return $this->belongsTo(get_class(new KpiTemplate()));
    }
}
