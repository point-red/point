<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SetupTenantDatabaseSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed {tenant_subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed required data for tenant';

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

        config()->set('database.connections.tenant.database', $tenantSubdomain);
        DB::connection('tenant')->reconnect();

        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'TenantDatabaseSeeder',
        ]);
    }
}
