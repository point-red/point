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
     *
     * @return mixed
     */
    public function handle()
    {
        $projects = Project::all();
        foreach ($projects as $project) {
            $db = env('DB_DATABASE').'_'.strtolower($project->code);
            $this->line('Alter ' . $db);

            config()->set('database.connections.tenant.database', $db);
            DB::connection('tenant')->reconnect();
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD `employee_status_id` integer(10) unsigned default null after `job_title`');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD `employee_job_location_id` integer(10) unsigned default null after `employee_status_id`');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD `daily_transport_allowance` decimal(65, 30) unsigned default 0 after `notes`');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD `functional_allowance` decimal(65, 30) unsigned default 0 after `daily_transport_allowance`');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD `communication_allowance` decimal(65, 30) unsigned default 0 after `functional_allowance`');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD `user_id` integer(10) unsigned default null after `communication_allowance`');
            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_indicators` ADD `automated_code` varchar(255) default null unique after `target`');
            DB::connection('tenant')->statement('ALTER TABLE `kpi_indicators` ADD `automated_code` varchar(255) default null unique after `target`');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_employee_status_id_foreign` FOREIGN KEY (`employee_status_id`) references employee_statuses (`id`) ON DELETE SET NULL');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_employee_job_location_id_foreign` FOREIGN KEY (`employee_job_location_id`) references employee_job_locations (`id`) ON DELETE SET NULL');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) references users (`id`) ON DELETE SET NULL');
        }
    }
}
