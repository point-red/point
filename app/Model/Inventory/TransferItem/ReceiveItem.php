<?php

namespace App\Model\Inventory\TransferItem;

use App\Model\Form;
use App\Exceptions\IsReferencedException;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Master\Item;
use App\Model\Inventory\TransferItem\TransferItem;
use App\Model\Inventory\TransferItem\ReceiveItemItem;
use App\Traits\Model\Inventory\InventoryReceiveItemJoin;
use App\Helpers\Inventory\InventoryHelper;

class ReceiveItem extends TransactionModel
{
    use InventoryReceiveItemJoin;

    public static $morphName = 'ReceiveItem';

    protected $connection = 'tenant';

    public static $alias = 'transfer_receive';

    public $timestamps = false;

    public $defaultNumberPrefix = 'TIRECEIVE';

    protected $fillable = [
        'warehouse_id',
        'from_warehouse_id',
        'transfer_item_id'
    ];
    
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function from_warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(ReceiveItemItem::class);
    }

    public function transfer_item()
    {
        return $this->belongsTo(TransferItem::class);
    }

    public function isAllowedToDelete()
    {
        // Check if not referenced by transfer receive
        if ($this->transfer_item->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by transfer receive', $this->receiveItem);
        }

    }

    public static function create($data)
    {
        $receiveItem = new self;
        $receiveItem->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $receiveItem->save();

        $receiveItem->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $receiveItem);

        return $receiveItem;
    }

    private static function mapItems($items)
    {
        $array = [];
        foreach ($items as $item) {
            array_push($array, $item);
        }
        
        return array_map(function ($item) {
            $receiveItemItem = new ReceiveItemItem;
            $receiveItemItem->fill($item);

            return $receiveItemItem;
        }, $array);
    }

    /**
     * Update price, cogs in inventory.
     *
     * @param $form
     * @param $transferItem
     */
    public static function updateInventory($form, $receiveItem)
    {
        foreach ($receiveItem->items as $item) {
            if ($item->quantity > 0) {
                $options = [];
                if ($item->item->require_expiry_date) {
                    $options['expiry_date'] = $item->expiry_date;
                }
                if ($item->item->require_production_number) {
                    $options['production_number'] = $item->production_number;
                }

                $options['quantity_reference'] = $item->quantity;
                $options['unit_reference'] = $item->unit;
                $options['converter_reference'] = $item->converter;

                
                InventoryHelper::increase($form, $item->ReceiveItem->warehouse, $item->item, $item->quantity, $item->unit, $item->converter, $options);
                
                $distributionWarehouse = Warehouse::where('name', 'DISTRIBUTION WAREHOUSE')->first();
                InventoryHelper::decrease($form, $distributionWarehouse, $item->item, $item->quantity, $item->unit, $item->converter, $options);
            }
        }
    }

    public static function updateJournal($receiveItem)
    {
        /**
         * Journal Table
         * -----------------------------------------------------
         * Account                            | Debit | Credit |
         * -----------------------------------------------------
         * 1. Inventory in distribution       |        |    v   | 
         * 2. Inventories                     |   v    |        | Master Item
         */
        foreach ($receiveItem->items as $receiveItemItem) {
            $itemAmount = $receiveItemItem->item->cogs($receiveItemItem->item_id) * $receiveItemItem->quantity;

            // 1. Inventory in distribution
            $journal = new Journal;
            $journal->form_id = $receiveItem->form->id;
            $journal->journalable_type = Item::$morphName;
            $journal->journalable_id = $receiveItemItem->item_id;
            $journal->chart_of_account_id = get_setting_journal('transfer item', 'inventory in distribution');
            $journal->credit = $itemAmount;
            $journal->save();

            // 2. Inventories
            $journal = new Journal;
            $journal->form_id = $receiveItem->form->id;
            $journal->journalable_type = Item::$morphName;
            $journal->journalable_id = $receiveItemItem->item_id;
            $journal->chart_of_account_id = $receiveItemItem->item->chart_of_account_id;
            $journal->debit = $itemAmount;
            $journal->save();
        }
    }
}
