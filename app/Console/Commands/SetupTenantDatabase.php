<?php

namespace App\Console\Commands;

use App\Model\Auth\Role;
use App\Model\Master\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupTenantDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:setup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup tenant database for the first time';

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
        // seeding default database for tenant
        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'TenantDatabaseSeeder',
        ]);

        log_object(Artisan::output());

        // Default role
        $role = Role::findByName('super admin', 'api');

        // Default user (owner of this project)
        $user = User::first();
        $user->assignRole($role);
    }
}
