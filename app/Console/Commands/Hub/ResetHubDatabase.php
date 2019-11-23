<?php

namespace App\Console\Commands\Hub;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetHubDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hub:database:reset {db_name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset tenant database';

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
        $this->line('START | RESET DATABASE');

        $dbName = $this->argument('db_name') ?? env('DB_DATABASE');

        $this->line('1/3. Delete database');
        Artisan::call('hub:database:delete', ['db_name' => $dbName]);
        $this->line('2/3. Create database');
        Artisan::call('hub:database:create', ['db_name' => $dbName]);
        $this->line('3/3. Migrate database');
        Artisan::call('migrate');

        $this->line('FINISHED | RESET DATABASE');
    }
}
