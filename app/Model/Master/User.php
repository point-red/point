<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\TenantUserJoin;
use App\Traits\Model\Master\TenantUserRelation;
use Illuminate\Support\Arr;
use Spatie\Permission\Traits\HasRoles;

class User extends MasterModel
{
    use HasRoles, TenantUserJoin, TenantUserRelation;

    protected $connection = 'tenant';

    protected $guard_name = 'api';

    protected $user_logs = false;

    protected $appends = ['full_name'];

    public static $alias = 'user';

    protected $casts = [
        'call' => 'double',
        'effective_call' => 'double',
        'value' => 'double',
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getPermissions()
    {
        $permissions = $this->getAllPermissions();
        $names = Arr::pluck($permissions, 'name');

        return $names;
    }
}
