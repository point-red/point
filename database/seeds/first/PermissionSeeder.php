<?php

use App\Model\Auth\Role;
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
        $this->setPluginPermission();

        $permissions = Permission::all();
        $role->syncPermissions($permissions);
    }

    private function setMasterPermission()
    {
        Permission::createIfNotExists('menu master');

        $allPermission = [
            'user', 'role', 'customer'
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

    private function setPluginPermission()
    {
        Permission::createIfNotExists('menu plugin');

        $this->setScaleWeightPermission();
        $this->setPinPointPermission();
    }

    private function setScaleWeightPermission()
    {
        Permission::createIfNotExists('menu scale weight');

        $allPermission = [
            'scale weight truck', 'scale weight item',
        ];

        foreach ($allPermission as $permission) {
            Permission::createIfNotExists('create '.$permission);
            Permission::createIfNotExists('read '.$permission);
            Permission::createIfNotExists('update '.$permission);
            Permission::createIfNotExists('delete '.$permission);
        }
    }

    private function setPinPointPermission()
    {
        Permission::createIfNotExists('menu pin point');

        $allPermission = [
            'pin point master',
            'pin point sales visitation form',
        ];

        Permission::createIfNotExists('read pin point sales visitation form report');
        Permission::createIfNotExists('read pin point sales visitation report');
        Permission::createIfNotExists('read pin point attendance report');
        Permission::createIfNotExists('notification pin point sales');
        Permission::createIfNotExists('notification pin point supervisor');

        foreach ($allPermission as $permission) {
            Permission::createIfNotExists('create '.$permission);
            Permission::createIfNotExists('read '.$permission);
            Permission::createIfNotExists('update '.$permission);
            Permission::createIfNotExists('delete '.$permission);
        }
    }
}
