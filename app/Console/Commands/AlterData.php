<?php

namespace App\Console\Commands;

use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AlterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:alter-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary';

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
            $this->line('Clone ' . $project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter ' . $project->code);
            config()->set('database.connections.tenant.database', 'point_' . strtolower($project->code));
            DB::connection('tenant')->reconnect();

            // TODO: ADD TAXABLE COLUMN IN ITEMS AND SERVICES
            // TODO: ADD NOTES IN FORM
            // TODO: EDIT EDITED NOTES IN FORM (FROM STRING TO TEXT)
            // TODO: ITEM (CODE & BARCODE UNIQUE)
            // $table->boolean('unit_default')->default(false);
            // $table->boolean('unit_default_purchase')->default(false);
            // $table->boolean('unit_default_sales')->default(false);
//            ALTER TABLE pin_point_sales_visitations ADD COLUMN is_repeat_order BOOLEAN DEFAULT false
//            DB::connection('tenant')->statement('ALTER TABLE `groups` ADD COLUMN code varchar(255)');
            DB::connection('tenant')->statement('ALTER TABLE `pin_point_sales_visitations` ADD COLUMN is_repeat_order BOOLEAN DEFAULT false AFTER payment_received');

            $this->line('Migrate ' . $project->code);
//            Artisan::call('tenant:migrate', ['db_name' => 'point_' . strtolower($project->code)]);
        }
    }
}
