<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $connection = 'mysql';

    public static $alias = 'plugin';
}
