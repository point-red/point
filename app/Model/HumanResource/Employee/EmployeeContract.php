<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;
use Carbon\Carbon;

class EmployeeContract extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get the employee that owns the contract.
     */
    public function employee()
    {
        return $this->belongsTo(get_class(new Employee()), 'employee_id');
    }

    public function getContractBeginAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setContractBeginAttribute($value)
    {
        $this->attributes['contract_begin'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public function getContractEndAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setContractEndAttribute($value)
    {
        $this->attributes['contract_end'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }
}
