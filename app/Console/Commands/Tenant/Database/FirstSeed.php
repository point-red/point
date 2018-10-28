<?php

namespace App\Console\Commands\Tenant\Database;

use App\Model\Auth\Role;
use App\Model\Master\User;
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

        $this->line('assign default role for owner');
        $this->assignDefaultRoleForOwner();
        $this->seedEmployeeData();
    }

    private function assignDefaultRoleForOwner()
    {
        // Default role
        $role = Role::findByName('super admin', 'api');

        // Default user (owner of this project)
        $this->user = User::first();
        $this->user->assignRole($role);
    }

    private function seedEmployeeData()
    {
        $religions = ['Christian', 'Catholic', 'Islam', 'Buddha', 'Hindu'];
        for ($i = 0; $i < count($religions); $i++) {
            DB::connection('tenant')->table('employee_religions')->insert([
                'name' => $religions[$i],
                'created_by' => $this->user->id,
                'updated_by' => $this->user->id,
            ]);
        }

        $maritalStatues = ['Single', 'Married'];
        for ($i = 0; $i < count($maritalStatues); $i++) {
            DB::connection('tenant')->table('employee_marital_statuses')->insert([
                'name' => $maritalStatues[$i],
                'created_by' => $this->user->id,
                'updated_by' => $this->user->id,
            ]);
        }

        $genders = ['Male', 'Female'];
        for ($i = 0; $i < count($genders); $i++) {
            DB::connection('tenant')->table('employee_genders')->insert([
                'name' => $genders[$i],
                'created_by' => $this->user->id,
                'updated_by' => $this->user->id,
            ]);
        }
    }
}
