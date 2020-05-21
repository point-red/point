<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeContract extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_contract';

    /**
     * Get the employee that owns the contract.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getContractBeginAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function setContractBeginAttribute($value)
    {
        $this->attributes['contract_begin'] = convert_to_server_timezone($value);
    }

    public function getContractEndAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function setContractEndAttribute($value)
    {
        $this->attributes['contract_end'] = convert_to_server_timezone($value);
    }
}
