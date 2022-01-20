<?php

namespace App\Console\Commands;

use App\Model\Auth\Permission;
use App\Model\Auth\Role;
use Illuminate\Console\Command;

class SyncPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permission for super admin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::createIfNotExists('super admin');
        $permissions = Permission::all();
        $role->syncPermissions($permissions);
    }
}
