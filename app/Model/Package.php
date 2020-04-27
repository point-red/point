<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $connection = 'mysql';

    public static $alias = 'package';
}
