<?php

namespace App\Console\Commands\Tenant\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Seed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed {db_name} {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run seed command for tenant in database';

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

        $this->line('seed ' . $this->argument('db_name'));
        $this->line('seed ' . $this->argument('class'));

        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => $this->argument('class'),
            '--force' => true,
        ]);
    }
}
