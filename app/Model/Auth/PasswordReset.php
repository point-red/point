<?php

namespace App\Model\Auth;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $connection = 'mysql';

    protected $table = 'password_resets';

    const UPDATED_AT = null;
}
