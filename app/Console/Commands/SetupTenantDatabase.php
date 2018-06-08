<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

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
        $tenantSubdomain = $this->argument('tenant_subdomain');

        $process = new Process('mysql -u '.env('DB_TENANT_USERNAME').' -p'.env('DB_TENANT_PASSWORD').' -e "drop database if exists '.$tenantSubdomain.'"');
        $process->run();

        // create new database
        $process = new Process('mysql -u '.env('DB_TENANT_USERNAME').' -p'.env('DB_TENANT_PASSWORD').' -e "create database '.$tenantSubdomain.'"');
        $process->run();

        config()->set('database.connections.tenant.database', $tenantSubdomain);

        Artisan::call('migrate:refresh', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
        ]);
    }
}
