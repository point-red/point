<?php

namespace App\Model\Auth;

use Illuminate\Database\Eloquent\Model;

class RoleHasPermission extends Model
{
    protected $connection = 'tenant';

    protected $table = 'role_has_permissions';

    public $timestamps = false;
}
