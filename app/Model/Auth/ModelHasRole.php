<?php

namespace App\Model\Auth;

use Illuminate\Database\Eloquent\Model;

class ModelHasRole extends Model
{
    protected $connection = 'tenant';

    protected $table = 'model_has_roles';

    public $timestamps = false;
}
