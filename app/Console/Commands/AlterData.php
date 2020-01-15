<?php

namespace App\Console\Commands;

use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
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
            $this->line('Clone '.$project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            $items = Item::all();
            foreach ($items as $item) {
                if ($item->units->count() == 0) {
                    $itemUnit = new ItemUnit;
                    $itemUnit->item_id = $item->id;
                    $itemUnit->name = 'PCS';
                    $itemUnit->label = 'PCS';
                    $itemUnit->converter = 1;
                    $itemUnit->save();
                }
            }
        }
    }

    public static function update($migration)
    {
        DB::connection('tenant')
            ->table('migrations')
            ->insert(['migration' => $migration, 'batch' => 1]);
    }
}
