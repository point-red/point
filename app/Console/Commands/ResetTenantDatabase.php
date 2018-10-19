<?php

namespace App\Console\Commands;

use App\Model\Master\User;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ResetTenantDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:reset-database {project}';

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
        $project = Project::where('code', $this->argument('project'))->first();

        // Recreate new database for tenant project
        $databaseName = 'point_'.strtolower($project->code);

        Artisan::call('tenant:create-database', [
            'db_name' => $databaseName,
        ]);

        // Update tenant database name in configuration
        config()->set('database.connections.tenant.database', $databaseName);
        DB::connection('tenant')->reconnect();
        DB::connection('tenant')->beginTransaction();

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        info('database migrated');

        // Clone user point into their database
        $owner = $project->owner;

        $user = new User;
        $user->id = $owner->id;
        $user->name = $owner->name;
        $user->email = $owner->email;
        $user->first_name = $owner->first_name;
        $user->last_name = $owner->last_name;
        $user->address = $owner->address;
        $user->phone = $owner->phone;
        $user->save();

        Artisan::call('tenant:seed-fresh-project');

        DB::connection('tenant')->commit();
    }
}
