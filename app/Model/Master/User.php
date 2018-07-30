<?php

namespace App\Model\Master;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'tenant';

    protected $guard_name = 'api';

    use HasRoles;

    public function getPermissions()
    {
        $permissions = $this->getAllPermissions();
        $names = array_pluck($permissions, 'name');

        return $names;
    }
}
