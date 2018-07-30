<?php

use App\Model\Auth\Role;
use App\Model\Master\User;
use App\Model\Auth\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::createIfNotExists('super admin');

        $this->setMasterPermission();
        $this->setHumanResourcePermission();

        $permissions = Permission::all();
        $role->syncPermissions($permissions);
    }

    private function setMasterPermission()
    {
        Permission::createIfNotExists('menu master');

        $allPermission = [
            'user', 'role',
        ];

        foreach ($allPermission as $permission) {
            Permission::createIfNotExists('create '.$permission);
            Permission::createIfNotExists('read '.$permission);
            Permission::createIfNotExists('update '.$permission);
            Permission::createIfNotExists('delete '.$permission);
        }
    }

    private function setHumanResourcePermission()
    {
        Permission::createIfNotExists('menu human resource');

        $allPermission = [
            'employee', 'employee kpi', 'employee assessment',
        ];

        foreach ($allPermission as $permission) {
            Permission::createIfNotExists('create '.$permission);
            Permission::createIfNotExists('read '.$permission);
            Permission::createIfNotExists('update '.$permission);
            Permission::createIfNotExists('delete '.$permission);
        }
    }
}
