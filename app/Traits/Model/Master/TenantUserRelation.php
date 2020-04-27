<?php

namespace App\Traits\Model\Master;

use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Branch;
use App\Model\Master\Warehouse;

trait TenantUserRelation
{
    /**
     * The employees that belong to the user.
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_scorer');
    }

    /**
     * The warehouses that belong to the user.
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouse')->withPivot(['is_default']);
    }

    /**
     * The branches that belong to the user.
     */
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user')->withPivot(['is_default']);
    }
}
