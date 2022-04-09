<?php

namespace App\Model\Master;

use App\Model\MasterModel;
use App\Traits\Model\Master\TenantUserJoin;
use App\Traits\Model\Master\TenantUserRelation;
use Illuminate\Support\Arr;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $first_name
 * @property string $last_name
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property int $branch_id
 * @property int $warehouse_id
 */
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
        return join(' ', array_filter([
            $this->first_name,
            $this->last_name,
        ]));
    }

    /**
     * @return string[]
     */
    public function getPermissions()
    {
        $permissions = $this->getAllPermissions();

        return Arr::pluck($permissions, 'name');
    }
}
