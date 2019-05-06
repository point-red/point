<?php

namespace App\Console\Commands;

use App\Model\Master\Item;
use Illuminate\Console\Command;
use App\Model\Accounting\Journal;
use App\Model\Inventory\Inventory;
use App\Model\Inventory\InventoryAudit\InventoryAudit;

class Recalculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // select all item in inventories
        $inventoryItems = Inventory::groupBy('item_id')->get();

        // calculate quantity
        foreach ($inventoryItems as $inventoryItem) {
            $this->calculateQuantity($inventoryItem->item_id);
        }

        // calculate value
        foreach ($inventoryItems as $inventoryItem) {
            $this->calculateValue($inventoryItem->item_id);
            // select all warehouse for each item
            $inventoryWarehouses = Inventory::where('item_id', '=', $inventoryItem->id)->groupBy('warehouse_id')->get();

            foreach ($inventoryWarehouses as $inventoryWarehouse) {
                // select inventory collection of specific item / warehouse
                $inventories = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
                    ->where('item_id', $inventoryItem->item_id)
                    ->where('warehouse_id', $inventoryWarehouse->warehouse_id)
                    ->orderBy()
                    ->get();

                foreach ($inventories as $inventory) {
                    // update value (recalculating process)
                    if ($inventory->formable_type == InventoryAudit::$morphName) {
                        // fix i/o value
                        $inventory->quantity = 0;
                    }

                    $inventory->cogs = 0;
                    $inventory->total_quantity = 0;
                    $inventory->total_value = 0;
                    $inventory->save();
                }
            }
        }
    }

    private function calculateQuantity($item_id)
    {
        $inventoryWarehouses = Inventory::where('item_id', '=', $item_id)->groupBy('warehouse_id')->get();

        foreach ($inventoryWarehouses as $inventoryWarehouse) {
            // select inventory collection of specific item / warehouse
            $inventories = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
                ->where('item_id', $item_id)
                ->where('warehouse_id', $inventoryWarehouse->warehouse_id)
                ->orderBy('forms.date')
                ->get();

            foreach ($inventories as $inventory) {
                // update value (recalculating process)
                if ($inventory->formable_type == InventoryAudit::$morphName) {
                    // fix i/o value
                    $inventory->quantity = 0;
                }

                $inventory->total_quantity = 0;
                $inventory->save();
            }
        }
    }

    private function calculateValue($item_id)
    {
        $inventories = Inventory::join('forms', 'forms.id', '=', 'inventories.form_id')
            ->where('item_id', $item_id)
            ->orderBy('forms.date')
            ->get();

        foreach ($inventories as $inventory) {
            // update value (recalculating process)
            $inventory->cogs = 0;
            $inventory->total_value = 0;
            $inventory->save();

            $this->updateJournal();
        }
    }

    private function updateJournal($formId, $itemId, $cogs)
    {
        $inventories = Journal::where('form_id', $formId)
            ->where('journalable_type', Item::$morphName)
            ->where('journalable_id', $itemId)
            ->where('journalable_id', $itemId)
            ->get();

        foreach ($inventories as $inventory) {
            $inventory->debit = $cogs;
            $inventory->save();
        }
    }
}
