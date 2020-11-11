<?php

namespace App\Console\Commands;

use App\Model\Inventory\Inventory;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Scalar\MagicConst\Line;

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
        $projects = Project::where('is_generated', true)->get();
        foreach ($projects as $project) {
            $this->line('Clone '.$project->code);
            // Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);

            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            
            DB::connection('tenant')->reconnect();
            DB::connection('tenant')->beginTransaction();
            

            $salesVisitations = SalesVisitation::where('payment_method', 'sell-out')->orWhere('payment_method', 'taking-order')->get();

            foreach ($salesVisitations as $salesVisitation) {
                $inventory = Inventory::where('form_id', $salesVisitation->form->number)->first();
                if ($inventory) {
                    $this->line($salesVisitation->form->number);
                }
            } 
            
            DB::connection('tenant')->commit();
        }
    }
}
