<?php

namespace App\Console\Commands;

use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SeedTenantDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed to all tenant database';

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
            $this->line(++$increment . '. Seed : ' . $project->code);
            config()->set('database.connections.tenant.database', 'point_' . $project->code);
            DB::connection('tenant')->reconnect();

            Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--class' => $this->argument('class'),
                '--force' => true
            ]);
        }
    }
}
