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
            DB::connection('tenant')->statement('ALTER TABLE `pos_bills` ADD COLUMN `warehouse_name` varchar(255) default null after `customer_name`');
            //
            DB::connection('tenant')->statement('ALTER TABLE `customers` DROP COLUMN `disabled`');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `archived_at` datetime default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD COLUMN `archived_by` integer(10) unsigned default null');
            DB::connection('tenant')->statement('ALTER TABLE `customers` ADD CONSTRAINT `customers_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            //
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` DROP COLUMN `disabled`');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `archived_at` datetime default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD COLUMN `archived_by` integer(10) unsigned default null');
            DB::connection('tenant')->statement('ALTER TABLE `suppliers` ADD CONSTRAINT `suppliers_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            //
            DB::connection('tenant')->statement('ALTER TABLE `warehouses` ADD COLUMN `archived_at` datetime default null');
            DB::connection('tenant')->statement('ALTER TABLE `warehouses` ADD COLUMN `archived_by` integer(10) unsigned default null');
            DB::connection('tenant')->statement('ALTER TABLE `warehouses` ADD CONSTRAINT `warehouses_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            //
            DB::connection('tenant')->statement('ALTER TABLE `allocations` DROP COLUMN `disabled`');
            DB::connection('tenant')->statement('ALTER TABLE `allocations` ADD COLUMN `archived_at` datetime default null');
            DB::connection('tenant')->statement('ALTER TABLE `allocations` ADD COLUMN `archived_by` integer(10) unsigned default null');
            DB::connection('tenant')->statement('ALTER TABLE `allocations` ADD CONSTRAINT `allocations_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            //
            DB::connection('tenant')->statement('ALTER TABLE `services` DROP COLUMN `disabled`');
            DB::connection('tenant')->statement('ALTER TABLE `services` ADD COLUMN `archived_at` datetime default null');
            DB::connection('tenant')->statement('ALTER TABLE `services` ADD COLUMN `archived_by` integer(10) unsigned default null');
            DB::connection('tenant')->statement('ALTER TABLE `services` ADD CONSTRAINT `services_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            //
            DB::connection('tenant')->statement('ALTER TABLE `items` DROP COLUMN `disabled`');
            DB::connection('tenant')->statement('ALTER TABLE `items` ADD COLUMN `archived_at` datetime default null');
            DB::connection('tenant')->statement('ALTER TABLE `items` ADD COLUMN `archived_by` integer(10) unsigned default null');
            DB::connection('tenant')->statement('ALTER TABLE `items` ADD CONSTRAINT `items_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            //
            DB::connection('tenant')->statement('ALTER TABLE `users` ADD COLUMN `created_by` integer(10) unsigned default null');
            DB::connection('tenant')->statement('ALTER TABLE `users` ADD CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            DB::connection('tenant')->statement('ALTER TABLE `users` ADD COLUMN `updated_by` integer(10) unsigned default null');
            DB::connection('tenant')->statement('ALTER TABLE `users` ADD CONSTRAINT `users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            DB::connection('tenant')->statement('ALTER TABLE `users` ADD COLUMN `archived_at` datetime default null');
            DB::connection('tenant')->statement('ALTER TABLE `users` ADD COLUMN `archived_by` integer(10) unsigned default null');
            DB::connection('tenant')->statement('ALTER TABLE `users` ADD CONSTRAINT `users_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
        }
    }
}
