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

            DB::connection('tenant')->statement('ALTER TABLE `chart_of_account_types` ADD CONSTRAINT `chart_of_account_type_name_unique` UNIQUE (`name`)');
            DB::connection('tenant')->statement('ALTER TABLE `chart_of_account_groups` ADD CONSTRAINT `chart_of_account_group_name_unique` UNIQUE (`name`)');
            DB::connection('tenant')->statement('ALTER TABLE `chart_of_accounts` ADD CONSTRAINT `chart_of_accounts_number_name_unique` UNIQUE (`number`,`name`)');
            DB::connection('tenant')->statement('ALTER TABLE `item_units` MODIFY `label` varchar(5)');
            DB::connection('tenant')->statement('ALTER TABLE `items` MODIFY `stock_reminder` decimal(65,30) unsigned NOT NULL default 0');
            DB::connection('tenant')->statement('ALTER TABLE `items` ADD `stock` decimal(65,30) unsigned NOT NULL default 0 after `disabled`');
            DB::connection('tenant')->statement('ALTER TABLE `items` ADD `taxable` tinyint(1) NOT NULL default 1 after `notes`');
            DB::connection('tenant')->statement('ALTER TABLE `items` ADD `unit_default` integer(10) unsigned after `stock_reminder`');
            DB::connection('tenant')->statement('ALTER TABLE `items` ADD `unit_default_purchase` integer(10) unsigned after `unit_default`');
            DB::connection('tenant')->statement('ALTER TABLE `items` ADD `unit_default_sales` integer(10) unsigned after `unit_default_purchase`');
            DB::connection('tenant')->statement('ALTER TABLE `groups` ADD `class_reference` varchar(255) not null after `type`');
            DB::connection('tenant')->statement('ALTER TABLE `groups` MODIFY `type` varchar(255) null');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD `notes` text after `edited_notes`');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD `increment` integer(10) unsigned not null after `done`');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD `increment_group` mediumint(8) unsigned not null after `increment`');
            DB::connection('tenant')->statement('ALTER TABLE `form_approvals` MODIFY `expired_at` datetime not null');
            DB::connection('tenant')->statement('ALTER TABLE `form_approvals` MODIFY `approval_at` datetime null');
            DB::connection('tenant')->statement('ALTER TABLE `form_approvals` ADD `approved` tinyint(1) default null after `approval_at`');
            DB::connection('tenant')->statement('ALTER TABLE `form_cancellations` MODIFY `expired_at` datetime not null');
            DB::connection('tenant')->statement('ALTER TABLE `form_cancellations` MODIFY `approval_at` datetime null');
            DB::connection('tenant')->statement('ALTER TABLE `form_cancellations` ADD `approved` tinyint(1) default null after `approval_at`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` ADD `form_id` integer(10) unsigned not null after `id`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` ADD `form_id_reference` integer(10) unsigned after `form_id`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` MODIFY `journalable_id` integer(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `journals` DROP FOREIGN KEY `journals_form_number_foreign`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` DROP INDEX `journals_form_number_index`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` DROP FOREIGN KEY `journals_form_number_reference_foreign`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` DROP INDEX `journals_form_number_reference_index`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` DROP COLUMN `form_number`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` DROP COLUMN `form_number_reference`');
            DB::connection('tenant')->statement('ALTER TABLE `journals` ADD CONSTRAINT `journals_form_id_foreign` FOREIGN KEY (`form_id`) references forms (`id`)');
            DB::connection('tenant')->statement('ALTER TABLE `journals` ADD INDEX `journals_form_id_index` (`form_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `journals` ADD CONSTRAINT `journals_form_id_reference_foreign` FOREIGN KEY (`form_id_reference`) references forms (`id`)');
            DB::connection('tenant')->statement('ALTER TABLE `journals` ADD INDEX `journals_form_id_reference_index` (`form_id_reference`)');
            DB::connection('tenant')->statement('ALTER TABLE `inventories` DROP COLUMN `date`');
            DB::connection('tenant')->statement('ALTER TABLE `inventories` DROP FOREIGN KEY `inventories_form_number_foreign`');
            DB::connection('tenant')->statement('ALTER TABLE `inventories` DROP COLUMN `form_number`');
            DB::connection('tenant')->statement('ALTER TABLE `inventories` ADD `form_id` integer(10) unsigned not null after `id`');
            DB::connection('tenant')->statement('ALTER TABLE `inventories` ADD CONSTRAINT `journals_form_id_foreign` FOREIGN KEY (`form_id`) references forms (`id`)');
            DB::connection('tenant')->statement('ALTER TABLE `inventories` ADD INDEX `journals_form_id_index` (`form_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `price_list_items` MODIFY `date` datetime not null');
            DB::connection('tenant')->statement('ALTER TABLE `price_list_services` MODIFY `date` datetime not null');
        }
    }
}
