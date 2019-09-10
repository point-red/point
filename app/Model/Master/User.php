<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use Spatie\Permission\Traits\HasRoles;

class User extends MasterModel
{
    protected $connection = 'tenant';

    protected $guard_name = 'api';

    protected $user_logs = false;

    use HasRoles;

    protected $casts = [
        'call' => 'double',
        'effective_call' => 'double',
        'value' => 'double',
    ];

    public function getPermissions()
    {
        $permissions = $this->getAllPermissions();
        $names = array_pluck($permissions, 'name');

        return $names;
    }

    /**
     * The employees that belong to the user.
     */
    public function employees()
    {
        return $this->belongsToMany('App\Model\HumanResource\Employee\Employee', 'employee_scorer');
    }
}
