<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;

class SetupTenantDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:setup-database {tenant_subdomain}';

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
        // tenant subdomain equal to tenant database name
        $tenantSubdomain = $this->argument('tenant_subdomain');

        // drop tenant database if exists
        $process = new Process('mysql -u '.env('DB_TENANT_USERNAME').' -p'.env('DB_TENANT_PASSWORD').' -e "drop database if exists '.$tenantSubdomain.'"');
        $process->run();

        // create new tenant database
        $process = new Process('mysql -u '.env('DB_TENANT_USERNAME').' -p'.env('DB_TENANT_PASSWORD').' -e "create database '.$tenantSubdomain.'"');
        $process->run();

        // update tenant database name in configuration
        config()->set('database.connections.tenant.database', $tenantSubdomain);

        // migrate database
        Artisan::call('migrate:refresh', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
        ]);

        $tenantSubdomain = $this->argument('tenant_subdomain');

        config()->set('database.connections.tenant.database', $tenantSubdomain);
        DB::connection('tenant')->reconnect();

        // seeding default database for tenant
        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'TenantDatabaseSeeder',
        ]);
    }
}
