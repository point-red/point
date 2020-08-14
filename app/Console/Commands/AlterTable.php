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
        $projects = Project::where('is_generated', true)->get();
        foreach ($projects as $project) {
            $db = env('DB_DATABASE').'_'.strtolower($project->code);
            $this->line('Clone '.$project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', $db);
            DB::connection('tenant')->reconnect();

            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `city` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `state` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `country` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `zip_code` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `latitude` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `longitude` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `phone_cc` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `email` varchar(255) default null');

            DB::connection('tenant')->statement('ALTER TABLE `customers` DROP COLUMN `credit_ceiling`');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `credit_limit` decimal(65, 30) default 0');

            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `city` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `state` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `country` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `zip_code` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `latitude` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `longitude` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `phone_cc` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `email` varchar(255) default null');

            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `tax_identification_number` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `city` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `state` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `country` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `zip_code` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `latitude` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `longitude` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `phone_cc` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `expeditions` ADD COLUMN `email` varchar(255) default null');

            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `tax_identification_number` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `address` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `city` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `state` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `country` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `zip_code` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `latitude` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `longitude` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `phone` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `phone_cc` varchar(255) default null');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `email` varchar(255) default null');
        }
    }
}
