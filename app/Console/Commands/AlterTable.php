<?php

namespace App\Console\Commands;

use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlterTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:alter-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix table';

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
     */
    public function handle()
    {
        DB::connection('mysql')->statement('ALTER TABLE `projects` ADD `whatsapp` VARCHAR(255) null after `phone`');
        DB::connection('mysql')->statement('ALTER TABLE `projects` ADD `website` VARCHAR(255) null after `whatsapp`');
        DB::connection('mysql')->statement('ALTER TABLE `projects` ADD `marketplace_notes` TEXT null after `website`');
//        $projects = Project::all();
//        foreach ($projects as $project) {
//            $db = env('DB_DATABASE').'_'.strtolower($project->code);
//            $this->line('Alter '.$db);
//
//            config()->set('database.connections.tenant.database', $db);
//            DB::connection('tenant')->reconnect();
//            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_indicators` DROP INDEX `kpi_template_indicators_automated_code_unique`');
//        }
    }
}
