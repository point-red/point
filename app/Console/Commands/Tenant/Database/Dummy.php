<?php

namespace App\Console\Commands\Tenant\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Dummy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed:dummy {db_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed dummy data for tenant';

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
        $this->line('Artisan call seed dummy database seeder');

        Artisan::call('db:seed', [
            '--database' => 'mysql',
            '--class' => 'DummyDatabaseSeeder',
        ]);

        $this->line(Artisan::output());

        $dbName = strtolower($this->argument('dbName'));

        config()->set('database.connections.tenant.database', $dbName);
        DB::connection('tenant')->reconnect();

        $this->line('Artisan call seed tenant dummy database seeder');

        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'DummyTenantDatabaseSeeder',
        ]);

        $this->line(Artisan::output());
    }
}
