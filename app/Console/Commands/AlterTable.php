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
            DB::connection('tenant')->statement('ALTER TABLE `items` ADD COLUMN `require_production_number` boolean default false after `disabled`');
            DB::connection('tenant')->statement('ALTER TABLE `items` ADD COLUMN `require_expiry_date` boolean default false after `require_production_number`');
            DB::connection('tenant')->statement('ALTER TABLE `inventories` ADD COLUMN `expiry_date` datetime default null after `quantity`');
            DB::connection('tenant')->statement('ALTER TABLE `inventories` ADD COLUMN `production_number` varchar(255) default null after `expiry_date`');
            DB::connection('tenant')->statement('ALTER TABLE `opening_stock_warehouses` ADD COLUMN `expiry_date` datetime default null after `quantity`');
            DB::connection('tenant')->statement('ALTER TABLE `opening_stock_warehouses` ADD COLUMN `production_number` varchar(255) default null after `expiry_date`');
            DB::connection('tenant')->statement('ALTER TABLE `inventory_audit_items` ADD COLUMN `expiry_date` datetime default null after `quantity`');
            DB::connection('tenant')->statement('ALTER TABLE `inventory_audit_items` ADD COLUMN `production_number` varchar(255) default null after `expiry_date`');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_receive_items` ADD COLUMN `expiry_date` datetime default null after `quantity`');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_receive_items` ADD COLUMN `production_number` varchar(255) default null after `expiry_date`');
            DB::connection('tenant')->statement('ALTER TABLE `delivery_note_items` ADD COLUMN `expiry_date` datetime default null after `quantity`');
            DB::connection('tenant')->statement('ALTER TABLE `delivery_note_items` ADD COLUMN `production_number` varchar(255) default null after `expiry_date`');
            DB::connection('tenant')->statement('ALTER TABLE `pos_bill_items` ADD COLUMN `expiry_date` datetime default null after `quantity`');
            DB::connection('tenant')->statement('ALTER TABLE `pos_bill_items` ADD COLUMN `production_number` varchar(255) default null after `expiry_date`');
            DB::connection('tenant')->statement('ALTER TABLE `pos_bills` ADD COLUMN `warehouse_id` integer(10) unsigned not null after `customer_id`');
            DB::connection('tenant')->statement('ALTER TABLE `pos_bills` ADD CONSTRAINT `pos_bills_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES warehouses (`id`) ON DELETE RESTRICT');
        }
    }
}
