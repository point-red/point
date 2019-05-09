<?php

namespace App\Console\Commands\Tenant\Database;

use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class Rollbacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:rollbacks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run rollback command for all tenant in database';

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
        $projects = Project::all();

        foreach ($projects as $project) {
            $databaseName = env('DB_DATABASE').'_'.strtolower($project->code);

            $this->line('Rollback '.$project->code);

            config()->set('database.connections.tenant.database', strtolower($databaseName));
            DB::connection('tenant')->reconnect();
            DB::connection('tenant')->beginTransaction();

            Artisan::call('migrate:rollback', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        }
    }
}
