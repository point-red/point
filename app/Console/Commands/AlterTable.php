<?php

namespace App\Console\Commands;

use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
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
        $projects = Project::all();
        foreach ($projects as $project) {
            $db = env('DB_DATABASE').'_'.strtolower($project->code);

            $this->line('Clone '.$project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', $db);
            DB::connection('tenant')->reconnect();

            DB::connection('tenant')->statement('ALTER TABLE `manufacture_output_finished_goods` DROP FOREIGN KEY `manufacture_output_finished_goods_input_finish_good_id_foreign`');
            DB::connection('tenant')->statement('ALTER TABLE `manufacture_output_finished_goods` DROP COLUMN `input_finish_good_id`');

            DB::connection('tenant')->statement('ALTER TABLE `manufacture_output_finished_goods` ADD COLUMN `input_finished_good_id` int(10) unsigned after `manufacture_output_id`');
            DB::connection('tenant')->statement('ALTER TABLE `manufacture_output_finished_goods` ADD INDEX (`input_finished_good_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `manufacture_output_finished_goods` ADD CONSTRAINT `forms_input_finished_good_id_foreign` FOREIGN KEY (`input_finished_good_id`) REFERENCES manufacture_input_finished_goods (`id`) ON DELETE RESTRICT');
        }
    }
}
