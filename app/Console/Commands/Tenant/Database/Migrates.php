<?php

namespace App\Console\Commands\Tenant\Database;

use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Migrates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migration command for all tenant in database';

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
            // Recreate new database for tenant project
            $databaseName = 'point_'.strtolower($project->code);

            // Update tenant database name in configuration
            config()->set('database.connections.tenant.database', strtolower($databaseName));
            DB::connection('tenant')->reconnect();
            DB::connection('tenant')->beginTransaction();

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            info($project->code.' migrated');
        }
    }
}
