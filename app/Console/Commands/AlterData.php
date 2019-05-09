<?php

namespace App\Console\Commands;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Item;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

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
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            $chartOfAccounts = ChartOfAccount::all();

            if ($chartOfAccounts->count() == 0) {
                $this->call('tenant:seeds', ['class' => 'ChartOfAccountSeeder']);
            }

            $items = Item::all();

            $account = ChartOfAccount::where('name', 'sediaan barang jadi (manufaktur)')->first();

            foreach ($items as $item) {
                if ($item->chart_of_account_id == null) {
                    $item->chart_of_account_id = $account->id;
                    $item->save();
                }
            }
        }
    }
}
