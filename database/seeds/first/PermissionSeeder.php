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
        $this->setPurchasePermission();
        $this->setSalesPermission();
        $this->setInventoryPermission();
        $this->setAccountingPermission();
        $this->setFinancePermission();
        $this->setHumanResourcePermission();
        $this->setPluginPermission();

        $permissions = Permission::all();
        $role->syncPermissions($permissions);
    }

    private function setMasterPermission()
    {
        Permission::createIfNotExists('menu master');

        $allPermission = [
            'user', 'role',
            'customer', 'supplier', 'expedition',
            'item', 'service',
            'allocation', 'warehouse',
        ];

        foreach ($allPermission as $permission) {
            Permission::createIfNotExists('create '.$permission);
            Permission::createIfNotExists('read '.$permission);
            Permission::createIfNotExists('update '.$permission);
            Permission::createIfNotExists('delete '.$permission);
        }
    }

    private function setPurchasePermission()
    {
        Permission::createIfNotExists('menu purchase');

        $allPermission = [
            'purchase request',
            'purchase contract',
            'purchase order',
            'purchase receive',
            'purchase invoice',
            'purchase down payment',
            'purchase return',
        ];

        foreach ($allPermission as $permission) {
            Permission::createIfNotExists('create '.$permission);
            Permission::createIfNotExists('read '.$permission);
            Permission::createIfNotExists('update '.$permission);
            Permission::createIfNotExists('delete '.$permission);
        }
    }

    private function setSalesPermission()
    {
        Permission::createIfNotExists('menu sales');

        $allPermission = [
            'sales quotation',
            'sales contract',
            'sales order',
            'delivery order',
            'delivery notes',
            'sales invoice',
            'sales down payment',
            'sales return',
        ];

        foreach ($allPermission as $permission) {
            Permission::createIfNotExists('create '.$permission);
            Permission::createIfNotExists('read '.$permission);
            Permission::createIfNotExists('update '.$permission);
            Permission::createIfNotExists('delete '.$permission);
        }
    }

    private function setInventoryPermission()
    {
        Permission::createIfNotExists('menu inventory');

        $allPermission = [
            'inventory report',
            'inventory audit',
            'stock correction',
            'transfer item',
            'inventory usage',
        ];

        foreach ($allPermission as $permission) {
            Permission::createIfNotExists('create '.$permission);
            Permission::createIfNotExists('read '.$permission);
            Permission::createIfNotExists('update '.$permission);
            Permission::createIfNotExists('delete '.$permission);
        }
    }

    private function setAccountingPermission()
    {
        Permission::createIfNotExists('menu accounting');

        $allPermission = [
            'chart of account',
            'cut off',
            'memo journal',
        ];

        Permission::createIfNotExists('read balance sheet');
        Permission::createIfNotExists('read general ledger');
        Permission::createIfNotExists('read sub ledger');
        Permission::createIfNotExists('read trial balance');
        Permission::createIfNotExists('read profit and loss');
        Permission::createIfNotExists('read ratio report');

        foreach ($allPermission as $permission) {
            Permission::createIfNotExists('create '.$permission);
            Permission::createIfNotExists('read '.$permission);
            Permission::createIfNotExists('update '.$permission);
            Permission::createIfNotExists('delete '.$permission);
        }
    }

    private function setFinancePermission()
    {
        Permission::createIfNotExists('menu payment');

        $allPermission = [
            'payment order',
            'cash advance',
            'cash',
            'bank',
        ];

        Permission::createIfNotExists('read debt aging report');
        Permission::createIfNotExists('read allocation report');

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
