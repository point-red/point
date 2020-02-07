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

            // TODO: ALTER SET NULL ON DELETE COA GROUP & TYPE
            DB::connection('tenant')->statement('ALTER TABLE `chart_of_accounts` ADD COLUMN `sub_ledger_id` integer(10) unsigned after `group_id`');
            DB::connection('tenant')->statement('ALTER TABLE `chart_of_accounts` ADD CONSTRAINT `chart_of_accounts_chart_of_account_sub_ledgers_id_foreign` FOREIGN KEY (`sub_ledger_id`) REFERENCES chart_of_account_sub_ledgers (`id`) ON DELETE SET NULL');

//            DB::connection('tenant')->statement('RENAME TABLE `manufacture_formula_finish_goods` TO `manufacture_formula_finished_goods`');
//            DB::connection('tenant')->statement('RENAME TABLE `manufacture_input_finish_goods` TO `manufacture_input_finished_goods`');
//            DB::connection('tenant')->statement('RENAME TABLE `manufacture_output_finish_goods` TO `manufacture_output_finished_goods`');
//
//            DB::connection('tenant')->statement('ALTER TABLE `manufacture_formula_raw_materials` ADD COLUMN `converter` decimal(65,30) not null');
//            DB::connection('tenant')->statement('ALTER TABLE `manufacture_formula_finished_goods` ADD COLUMN `converter` decimal(65,30) not null');
//            DB::connection('tenant')->statement('ALTER TABLE `manufacture_input_raw_materials` ADD COLUMN `converter` decimal(65,30) not null');
//            DB::connection('tenant')->statement('ALTER TABLE `manufacture_input_finished_goods` ADD COLUMN `converter` decimal(65,30) not null');
//            DB::connection('tenant')->statement('ALTER TABLE `manufacture_output_finished_goods` ADD COLUMN `converter` decimal(65,30) not null');
//
//            DB::connection('tenant')->statement('ALTER TABLE `inventories` ADD COLUMN `quantity_reference` decimal(65,30) not null');
//            DB::connection('tenant')->statement('ALTER TABLE `inventories` ADD COLUMN `unit_reference` varchar(255) not null');
//            DB::connection('tenant')->statement('ALTER TABLE `inventories` ADD COLUMN `converter_reference` decimal(65,30) not null');
        }
    }
}
