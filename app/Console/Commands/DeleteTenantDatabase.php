<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DeleteTenantDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:delete-database {db_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete tenant database';

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
        $dbName = $this->argument('db_name');

        // drop tenant database if exists
        $process = new Process('mysql -u '.env('DB_TENANT_USERNAME').' -p'.env('DB_TENANT_PASSWORD').' -e "drop database if exists '.$dbName.'"');
        $process->run();
    }
}
