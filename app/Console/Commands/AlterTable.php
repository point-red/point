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

            DB::connection('tenant')->statement('ALTER TABLE `phones` ADD INDEX `phones_phoneable_id_index` (`phoneable_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `addresses` ADD INDEX `addresses_addressable_id_index` (`addressable_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `contact_people` ADD INDEX `contact_people_contactable_id_index` (`contactable_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `emails` ADD INDEX `emails_emailable_id_index` (`emailable_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `banks` ADD INDEX `banks_bankable_id_index` (`bankable_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `groupables` ADD INDEX `groupables_groupable_id_index` (`groupable_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `forms` ADD INDEX `forms_formable_id_index` (`formable_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `journals` ADD INDEX `journals_journalable_id_index` (`journalable_id`)');
            DB::connection('tenant')->statement('ALTER TABLE `master_histories` ADD INDEX `master_histories_historyable_id_index` (`historyable_id`)');
        }
    }
}
