<?php

namespace App\Model\Auth;

use Illuminate\Database\Eloquent\Model;

class ModelHasPermission extends Model
{
    protected $connection = 'tenant';

    protected $table = 'model_has_permissions';

    public $timestamps = false;
}
