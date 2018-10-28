<?php

namespace App\Console\Commands\Tenant\Database;

use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Reset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:database:reset {project_code}';

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
        $dbName = 'point_'.strtolower($project->code);
        Artisan::call('tenant:database:create', ['db_name' => $dbName]);
        Artisan::call('tenant:migrate', ['db_name' => $dbName]);

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

        Artisan::call('tenant:seed:first', ['db_name' => $dbName]);

        DB::connection('tenant')->commit();
    }
}
