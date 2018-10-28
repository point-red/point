<?php

namespace App\Console\Commands\Tenant\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate {db_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migration for tenant';

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
        // Update tenant database name in configuration
        config()->set('database.connections.tenant.database', strtolower($this->argument('db_name')));
        DB::connection('tenant')->reconnect();
        DB::connection('tenant')->beginTransaction();

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        info('Migration success');
    }
}
