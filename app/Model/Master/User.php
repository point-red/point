<?php

namespace App\Model\Master;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'tenant';
}
