<?php

namespace App\Console\Commands;

use App\Model\Master\Item;
use Illuminate\Console\Command;
use App\Model\Master\ItemDetail;
use App\Model\Inventory\Inventory;

class RecalculateStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:recalculate:stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate Stock';

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

        // calculate stock
        foreach ($inventoryItems as $inventoryItem) {
            $this->calculateStock($inventoryItem->item_id);
        }
    }

    private function calculateStock($item_id)
    {
        $inventoryProductionNoExpiryDate = Inventory::where('item_id', '=', $item_id)->groupBy('production_number', 'expiry_date')->get();

        $itemStock = 0;

        foreach ($inventoryProductionNoExpiryDate as $inventorySpecific) {
            // select inventory collection of specific item / warehouse
            $inventories = Inventory::where('item_id', $item_id)
                ->where('production_number', $inventorySpecific->production_number)
                ->where('expiry_date', $inventorySpecific->expiry_date)
                ->get();

            $itemStockByProductionNoExpiryDate = 0;

            foreach ($inventories as $inventory) {
                $itemStockByProductionNoExpiryDate += $inventory->quantity;
                $itemStock += $inventory->quantity;
            }

            ItemDetail::where('production_number', $inventorySpecific->production_number)->where('expiry_date', $inventorySpecific->expiry_date)->update(['stock' => $itemStockByProductionNoExpiryDate]);
        }

        Item::where('id', $item_id)->update(['stock' => $itemStock]);
    }
}
