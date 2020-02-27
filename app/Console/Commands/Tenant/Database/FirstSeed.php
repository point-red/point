<?php

namespace App\Console\Commands\Tenant\Database;

use App\Model\Auth\Role;
use App\Model\Master\Branch;
use App\Model\Master\PricingGroup;
use App\Model\Master\User;
use App\Model\Master\Warehouse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class FirstSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed:first {db_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed new tenant (project) with required data';

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
        config()->set('database.connections.tenant.database', strtolower($this->argument('db_name')));
        DB::connection('tenant')->reconnect();

        $this->line('Seeding Tenant database seeder');
        // seeding default database for tenant
        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'TenantDatabaseSeeder',
            '--force' => true,
        ]);

        if (PricingGroup::all()->count() == 0) {
            $pricingGroup = new PricingGroup;
            $pricingGroup->label = 'DEFAULT';
            $pricingGroup->save();
        }
        // seed branch
        $branch = new Branch;
        $branch->name = 'CENTRAL';
        $branch->save();

        // seed warehouse
        $warehouse = new Warehouse;
        $warehouse->name = 'CENTRAL WAREHOUSE';
        $warehouse->save();

        $this->line('assign default role for owner');
        $this->assignDefaultRoleForOwner();
    }

    private function assignDefaultRoleForOwner()
    {
        // Default role
        $role = Role::findByName('super admin', 'api');

        // Default user (owner of this project)
        $this->user = User::first();
        $this->user->assignRole($role);
    }
}
