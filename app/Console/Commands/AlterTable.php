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
        $projects = Project::where('id', '>', 7)->get();
        foreach ($projects as $project) {
            $db = env('DB_DATABASE').'_'.strtolower($project->code);

            $this->line('Clone '.$project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', $db);
            DB::connection('tenant')->reconnect();

            // TODO: REMOVE FIELD CREATED BY AND UPDATED BY IN CUTOFF
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` DROP FOREIGN KEY `cut_offs_updated_by_foreign`');
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` DROP COLUMN `date`');
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` DROP COLUMN `number`');
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` DROP INDEX `cut_offs_created_by_index`');
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` DROP INDEX `cut_offs_updated_by_index`');
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` DROP COLUMN `created_by`');
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` DROP COLUMN `updated_by`');
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` DROP COLUMN `created_at`');
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` DROP COLUMN `updated_at`');

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
