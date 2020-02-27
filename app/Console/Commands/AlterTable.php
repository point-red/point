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

            DB::connection('tenant')->statement('ALTER TABLE `users` ADD COLUMN `branch_id` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `users` ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES branches (`id`) ON DELETE SET NULL');

            DB::connection('tenant')->statement('ALTER TABLE `users` ADD COLUMN `warehouse_id` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `users` ADD CONSTRAINT `users_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES warehouses (`id`) ON DELETE SET NULL');

            DB::connection('tenant')->statement('ALTER TABLE `warehouses` ADD COLUMN `branch_id` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `warehouses` ADD CONSTRAINT `warehouses_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES branches (`id`) ON DELETE SET NULL');

            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `branch_id` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD CONSTRAINT `suppliers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES branches (`id`) ON DELETE SET NULL');

            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `branch_id` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD CONSTRAINT `customers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES branches (`id`) ON DELETE SET NULL');

            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN `branch_id` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES branches (`id`) ON DELETE SET NULL');

            DB::connection('tenant')->statement('ALTER TABLE `allocations` ADD COLUMN `branch_id` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `allocations` ADD CONSTRAINT `allocations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES branches (`id`) ON DELETE SET NULL');

            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `branch_id` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD CONSTRAINT `forms_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES branches (`id`) ON DELETE RESTRICT');
        }
    }
}
