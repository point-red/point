<?php

namespace App\Model\HumanResource\Employee;

use App\Model\MasterModel;

class EmployeeSocialMedia extends MasterModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_social_media';

    /**
     * Get the employee that owns the social media.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
