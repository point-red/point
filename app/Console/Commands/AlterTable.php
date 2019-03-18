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
            $this->line('Alter ' . $project->code);
            config()->set('database.connections.tenant.database', 'point_' . strtolower($project->code));
            DB::connection('tenant')->reconnect();

            DB::connection('tenant')->statement('ALTER TABLE `employees` MODIFY COLUMN `join_date` datetime');
            DB::connection('tenant')->statement('ALTER TABLE `employee_promotion_histories` MODIFY COLUMN `date` datetime');
            DB::connection('tenant')->statement('ALTER TABLE `employee_salary_histories` MODIFY COLUMN `date` datetime');
            DB::connection('tenant')->statement('ALTER TABLE `employee_training_histories` MODIFY COLUMN `date` datetime');
            DB::connection('tenant')->statement('ALTER TABLE `employee_contracts` MODIFY COLUMN `contract_begin` datetime');
            DB::connection('tenant')->statement('ALTER TABLE `employee_contracts` MODIFY COLUMN `contract_end` datetime');
            DB::connection('tenant')->statement('ALTER TABLE `kpis` MODIFY COLUMN `date` datetime');
            DB::connection('tenant')->statement('ALTER TABLE `pin_point_sales_visitations` MODIFY COLUMN `due_date` datetime');
        }
    }
}
