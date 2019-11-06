<?php

namespace App\Model\Auth;

use App\Traits\EloquentFilters;

class Role extends \Spatie\Permission\Models\Role
{
    protected $connection = 'tenant';

    use EloquentFilters;

    public static function isExists($name)
    {
        if (! self::where('name', $name)->first()) {
            return false;
        }

        return true;
    }

    public static function createIfNotExists($name)
    {
        if (! self::isExists($name)) {
            self::create(['name' => $name, 'guard_name' => 'api']);
        }

        return self::where('name', $name)->first();
    }
}
