<?php

namespace App\Model\Auth;

use App\Traits\EloquentFilters;
use App\Traits\Model\Auth\RoleJoin;
use App\Traits\Model\Auth\RoleRelation;

class Role extends \Spatie\Permission\Models\Role
{
    use EloquentFilters, RoleJoin, RoleRelation;

    protected $connection = 'tenant';

    public static $alias = 'role';

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

    public static function getTableName($column = null)
    {
        $tableName = with(new static)->getTable();

        if (isset($column)) {
            $tableName = "$tableName.$column";
        }

        return $tableName;
    }
}
