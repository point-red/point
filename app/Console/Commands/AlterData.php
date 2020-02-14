<?php

namespace App\Console\Commands;

use App\Model\Accounting\CutOffInventory;
use App\Model\Master\ItemUnit;
use App\Model\Master\PricingGroup;
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

            if (PricingGroup::all()->count() == 0) {
                $pricingGroup = new PricingGroup;
                $pricingGroup->label = 'DEFAULT';
                $pricingGroup->save();
            }

            $cutOffInventories = CutOffInventory::all();
            foreach ($cutOffInventories as $cutOffInventory) {
                if (!ItemUnit::where('item_id', $cutOffInventory->item_id)->first()) {
                    $itemUnit = new ItemUnit();
                    $itemUnit->item_id = $cutOffInventory->item_id;
                    $itemUnit->label = $cutOffInventory->unit;
                    $itemUnit->name = $cutOffInventory->unit;
                    $itemUnit->converter = $cutOffInventory->converter;
                    $itemUnit->created_by = $cutOffInventory->cutOff->form->created_by;
                    $itemUnit->updated_by = $cutOffInventory->cutOff->form->updated_by;
                    $itemUnit->save();

                    $cutOffInventory->item->unit_default = $itemUnit->id;
                    $cutOffInventory->item->unit_default_purchase = $itemUnit->id;
                    $cutOffInventory->item->unit_default_sales = $itemUnit->id;
                    $cutOffInventory->item->save();
                }
            }
        }
    }
}
