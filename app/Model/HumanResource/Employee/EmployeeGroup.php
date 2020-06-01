<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;
use App\Traits\Model\HumanResource\EmployeeGroupJoin;
use App\Traits\Model\HumanResource\EmployeeGroupRelation;

class EmployeeGroup extends MasterModel
{
    use EmployeeGroupJoin, EmployeeGroupRelation;

    protected $connection = 'tenant';

    protected $fillable = ['name'];

    public static $alias = 'employee_group';
}
