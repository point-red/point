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
//            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', $db);
            DB::connection('tenant')->reconnect();

            DB::connection('tenant')->statement('ALTER TABLE `forms` DROP COLUMN `approved`');
            DB::connection('tenant')->statement('ALTER TABLE `forms` DROP COLUMN `canceled`');

            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `request_approval_to` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD INDEX (`request_approval_to`)');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD CONSTRAINT `forms_request_approval_to_foreign` FOREIGN KEY (`request_approval_to`) REFERENCES users (`id`) ON DELETE RESTRICT');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `approval_by` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD INDEX (`approval_by`)');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD CONSTRAINT `forms_approval_by_foreign` FOREIGN KEY (`approval_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `approval_at` datetime default null');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `approval_reason` text');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `approval_status` tinyint(4) default 0');

            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `request_cancellation_to` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD INDEX (`request_cancellation_to`)');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD CONSTRAINT `forms_request_cancellation_to_foreign` FOREIGN KEY (`request_cancellation_to`) REFERENCES users (`id`) ON DELETE RESTRICT');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `request_cancellation_by` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD INDEX (`request_cancellation_by`)');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD CONSTRAINT `forms_request_cancellation_by_foreign` FOREIGN KEY (`request_cancellation_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `request_cancellation_at` datetime default null');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `request_cancellation_reason` text');

            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `cancellation_approval_by` int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD INDEX (`cancellation_approval_by`)');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD CONSTRAINT `forms_cancellation_approval_by_foreign` FOREIGN KEY (`cancellation_approval_by`) REFERENCES users (`id`) ON DELETE RESTRICT');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `cancellation_approval_at` datetime default null');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `cancellation_approval_reason` text');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD COLUMN `cancellation_status` tinyint(4) default 0');
        }
    }
}
