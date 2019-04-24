<?php

namespace App\Console\Commands;

use App\User;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use App\Model\Project\ProjectUser;
use Illuminate\Support\Facades\Artisan;

class NewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:new {database_name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup new development';

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
        $dbName = $this->argument('database_name') ?? env('DB_DATABASE');

        $this->line('create '.$dbName.' database');
        Artisan::call('tenant:database:delete', ['db_name' => $dbName]);
        Artisan::call('tenant:database:create', ['db_name' => $dbName]);
        Artisan::call('migrate');
        Artisan::call('passport:install');

        $this->line('setup new user "admin" and password "admin"');
        $user = new User;
        $user->name = 'admin';
        $user->first_name = 'admin';
        $user->last_name = 'admin';
        $user->email = 'admin@point';
        $user->password = bcrypt('admin');
        $user->save();

        $this->line('setup new project "dev"');
        $project = new Project;
        $project->owner_id = $user->id;
        $project->code = 'dev';
        $project->name = 'development';
        $project->save();

        $this->line('link owner project');
        $projectUser = new ProjectUser;
        $projectUser->project_id = $project->id;
        $projectUser->user_id = $user->id;
        $projectUser->user_name = $user->name;
        $projectUser->user_email = $user->email;
        $projectUser->joined = true;
        $projectUser->save();

        Artisan::call('tenant:database:reset', ['project_code' => 'dev']);
    }
}
