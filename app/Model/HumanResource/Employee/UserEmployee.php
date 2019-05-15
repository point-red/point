<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class UserEmployee extends MasterModel
{
    protected $connection = 'tenant';

    protected $table = 'user_employee';
}
