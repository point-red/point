<?php

namespace App\Console\Commands\Tenant\Database;

use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Seeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seeds {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run seed command for all tenant in database';

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
        $increment = 0;

        $projects = Project::all();

        $this->line('Total Project : ' . $projects->count());

        foreach ($projects as $project) {
            $this->line(++$increment.'. Seed : ' . $project->code);
            config()->set('database.connections.tenant.database', 'point_' . strtolower($project->code));
            DB::connection('tenant')->reconnect();

            Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--class' => $this->argument('class'),
                '--force' => true,
            ]);
        }
    }
}
