<?php

namespace App\Console\Commands\Tenant\Database;

use App\Model\Master\User;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

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
        $this->line('Start reset database tenant');
        $project = Project::where('code', $this->argument('project_code'))->first();

        if (! $project) {
            $this->line('There is no project "'.strtolower($this->argument('project_code')).'" in database');

            return;
        }

        $this->line('Reset project '.$project->code.' started');

        DB::connection('tenant')->beginTransaction();

        // Recreate new database for tenant project
        $dbName = env('DB_DATABASE').'_'.strtolower($project->code);
        $this->line('1/4. Recreate database');
        Artisan::call('tenant:database:delete', ['db_name' => $dbName]);
        Artisan::call('tenant:database:create', ['db_name' => $dbName]);
        $this->line('2/4. Migrate database');
        Artisan::call('tenant:migrate', ['db_name' => $dbName]);

        // Clone user point into their database
        $owner = $project->owner;

        $this->line('3/4. Add owner into user table');
        $user = new User;
        $user->id = $owner->id;
        $user->name = $owner->name;
        $user->email = $owner->email;
        $user->first_name = $owner->first_name;
        $user->last_name = $owner->last_name;
        $user->address = $owner->address;
        $user->phone = $owner->phone;
        $user->save();

        $this->line('4/4. Seed required data');
        Artisan::call('tenant:seed:first', ['db_name' => $dbName]);

        DB::connection('tenant')->commit();

        $this->line('Reset project '.$project->code.' finished');
    }
}
