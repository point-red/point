<?php

namespace App\Console\Commands;

use App\Model\Inventory\Inventory;
use App\Model\Master\Warehouse;
use App\Model\Project\Project;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use Illuminate\Console\Command;
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
        $projects = Project::where('is_generated', true)->where('code', 'kopibara')->get();
        foreach ($projects as $project) {
            $this->line('Clone '.$project->code);
            // Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);

            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            
            DB::connection('tenant')->reconnect();
            DB::connection('tenant')->beginTransaction();
            
            $invoices = PurchaseInvoice::all();

            foreach($invoices as $invoice) {
                $aCount = count($invoice->items);
                $bCount = Inventory::where('form_id', '=', $invoice->form->id)->count();
                if ($invoice->form->cancellation_approval_at === null && $aCount < $bCount) {
                    $this->line($invoice->form->number . ' : '. $aCount . ' = ' . $bCount . ' @' . $invoice->form->createdBy->name);
                }
            }

            $warehouses = Warehouse::all();

            foreach ($warehouses as $warehouse) {
                $this->line('warehouse ' . $warehouse->id);
 // SEARCH ALL INVENTORY DNA
 $inventories = Inventory::groupBy('production_number')->get();

 foreach($inventories as $inventory) {
     $sum = Inventory::where('production_number', '=', $inventory->production_number)
         ->where('item_id', $inventory->item_id)
         ->where('warehouse_id', $warehouse->id)
         ->sum('quantity');
     // FIND INVENTORY PROBLEM
     if ($sum < 0) {
         $this->line('1. '.$inventory->item->code . ' | ' . $inventory->production_number . ' = ' . $sum);

         $inventories2 = Inventory::where('item_id', $inventory->item_id)->where('warehouse_id', $warehouse->id)->groupBy('production_number')->get();
         foreach($inventories2 as $inventory2) {
             $sum2 = Inventory::where('production_number', '=', $inventory2->production_number)
                 ->where('item_id', $inventory2->item_id)
                 ->where('warehouse_id', $warehouse->id)
                 ->sum('quantity');
             if ($sum2 > ($sum * -1)) {
                 $this->line('2. '.$inventory2->item->code . ' | ' . $inventory2->production_number . ' | ' . $inventory2->expiry_date . ' = ' . $sum2);
                 break;
             }
         }

         // TARGET INVENTORY TO FIX
         $fixInventories = Inventory::where('production_number', '=', $inventory->production_number)
             ->where('item_id', $inventory->item_id)
             ->where('quantity', '<', 0)
             ->where('warehouse_id', $warehouse->id)
             ->sortBy('created_at', 'desc')
             ->get();

         foreach ($fixInventories as $fixInventory) {
             
             if ($sum < 0) {
                 if ($fixInventory->quantity < $sum) {
                     // Final
                     $this->line('1.1. '.$fixInventory->id . ' = '. $fixInventory->quantity);
                     $sum = 0;
                 } else {
                     // Need additional row
                     $this->line('1.2. '.$fixInventory->id . ' = '. $fixInventory->quantity);
                     $sum -= $fixInventory->quantity;
                 }
             }
         }
     }
 }
            }

           

            DB::connection('tenant')->commit();
        }
    }
}
