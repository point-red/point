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

            DB::connection('tenant')->statement('ALTER TABLE `stock_correction_items` DROP COLUMN IF EXISTS `price`');
            DB::connection('tenant')->statement('ALTER TABLE `transfer_item_items` DROP COLUMN IF EXISTS `price`');
            DB::connection('tenant')->statement('ALTER TABLE `receive_item_items` DROP COLUMN IF EXISTS `price`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` DROP COLUMN IF EXISTS `sub_ledger_id`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` DROP COLUMN IF EXISTS `sub_ledger_type`');

            DB::connection('tenant')->statement('ALTER TABLE `inventory_usage_items` ADD COLUMN `chart_of_account_id` integer(10) unsigned default null');
            DB::connection('tenant')->statement('ALTER TABLE `inventory_usage_items` ADD CONSTRAINT `inventory_usage_items_chart_of_account_id_foreign` FOREIGN KEY (`chart_of_account_id`) REFERENCES chart_of_accounts (`id`) ON DELETE RESTRICT');
        }
    }
}
