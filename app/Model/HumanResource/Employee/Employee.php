<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;
use App\Traits\Model\HumanResource\EmployeeJoin;
use App\Traits\Model\HumanResource\EmployeeRelation;

class Employee extends MasterModel
{
    use EmployeeJoin, EmployeeRelation;

    protected $connection = 'tenant';

    protected $appends = ['label'];

    protected $casts = [
        'daily_transport_allowance' => 'double',
        'functional_allowance' => 'double',
        'communication_allowance' => 'double',
    ];

    public static $alias = 'employee';

    public static $morphName = 'Employee';

    public function getLabelAttribute()
    {
        $label = $this->code ? '[' . $this->code . '] ' : '';

        return $label . $this->name;
    }
}
