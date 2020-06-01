<?php

namespace App\Console\Commands;

use App\Model\Inventory\Inventory;
use App\Model\Manufacture\ManufactureInput\ManufactureInput;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:check-data';

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
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            $inventory = Inventory::all()->count();
            $input = ManufactureInput::all()->count();

            if ($inventory > 0 || $input > 0) {
                $this->line('check '.$project->code);
                $this->line('inventory = '.$inventory);
                $this->line('manufacture = '.$input);

                $this->line('-------');
            }
        }
    }
}
