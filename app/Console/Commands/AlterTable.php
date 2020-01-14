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
            DB::connection('tenant')->statement('ALTER TABLE `purchase_requests` ADD COLUMN `supplier_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_requests` ADD COLUMN `supplier_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_orders` ADD COLUMN `supplier_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_orders` ADD COLUMN `supplier_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_contracts` ADD COLUMN `supplier_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_contracts` ADD COLUMN `supplier_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_invoices` ADD COLUMN `supplier_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_invoices` ADD COLUMN `supplier_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_returns` ADD COLUMN `supplier_name` varchar(255) not null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_returns` ADD COLUMN `supplier_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_returns` ADD COLUMN `supplier_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_receives` ADD COLUMN `supplier_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_receives` ADD COLUMN `supplier_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_down_payments` ADD COLUMN `supplier_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `purchase_down_payments` ADD COLUMN `supplier_phone` varchar(255) default null');

            DB::connection('tenant')->statement('ALTER TABLE `sales_quotations` ADD COLUMN `customer_name` varchar(255) not null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_quotations` ADD COLUMN `customer_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_quotations` ADD COLUMN `customer_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_orders` ADD COLUMN `customer_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_orders` ADD COLUMN `customer_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `delivery_orders` ADD COLUMN `customer_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `delivery_orders` ADD COLUMN `customer_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `delivery_notes` ADD COLUMN `customer_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `delivery_notes` ADD COLUMN `customer_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_invoices` ADD COLUMN `customer_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_invoices` ADD COLUMN `customer_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_returns` ADD COLUMN `customer_name` varchar(255) not null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_returns` ADD COLUMN `customer_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_returns` ADD COLUMN `customer_phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_down_payments` ADD COLUMN `customer_address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `sales_down_payments` ADD COLUMN `customer_phone` varchar(255) default null');
        }
    }
}
