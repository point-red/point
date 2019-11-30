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
        $projects = Project::all();
        foreach ($projects as $project) {
            $db = env('DB_DATABASE').'_'.strtolower($project->code);
            $this->line('Alter '.$db);

            config()->set('database.connections.tenant.database', $db);
            DB::connection('tenant')->reconnect();
            DB::connection('tenant')->statement('ALTER TABLE `sales_contract_group_items` DROP FOREIGN KEY `sales_contract_group_items_group_id_foreign`');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_contract_group_items` DROP FOREIGN KEY `purchase_contract_group_items_group_id_foreign`');
            DB::connection('tenant')->statement('ALTER TABLE `sales_contract_group_items` DROP COLUMN `group_id`');
            DB::connection('tenant')->statement('ALTER TABLE `sales_contract_group_items` DROP COLUMN `group_name`');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_contract_group_items` DROP COLUMN `group_id`');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_contract_group_items` DROP COLUMN `group_name`');
            DB::connection('tenant')->statement('ALTER TABLE `sales_contract_group_items` ADD COLUMN `item_group_id` integer unsigned AFTER `sales_contract_id`');
            DB::connection('tenant')->statement('ALTER TABLE `sales_contract_group_items` ADD CONSTRAINT `sales_contract_group_items_item_group_id_foreign` FOREIGN KEY (`item_group_id`) REFERENCES item_groups (`id`) ON DELETE CASCADE');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_contract_group_items` ADD COLUMN `item_group_id` integer unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_contract_group_items` ADD CONSTRAINT `purchase_contract_group_items_item_group_id_foreign` FOREIGN KEY (`item_group_id`) REFERENCES item_groups (`id`) ON DELETE CASCADE');
            DB::connection('tenant')->statement('DROP TABLE `groupables`');
            DB::connection('tenant')->statement('DROP TABLE `groups`');
        }
    }
}
