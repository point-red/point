<?php

namespace App\Model\Master;

use App\Model\HumanResource\Employee\Employee;
use App\Model\MasterModel;
use Illuminate\Support\Arr;
use Spatie\Permission\Traits\HasRoles;

class User extends MasterModel
{
    protected $connection = 'tenant';

    protected $guard_name = 'api';

    protected $user_logs = false;

    protected $appends = ['full_name'];

    use HasRoles;

    protected $casts = [
        'call' => 'double',
        'effective_call' => 'double',
        'value' => 'double',
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getPermissions()
    {
        $permissions = $this->getAllPermissions();
        $names = Arr::pluck($permissions, 'name');

        return $names;
    }

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
        return $this->belongsToMany(Warehouse::class, 'user_warehouse');
    }
}
