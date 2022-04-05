<?php

namespace App\Model\Inventory\TransferItem;

use App\Model\Form;
use App\Exceptions\IsReferencedException;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Master\Item;
use App\Model\Inventory\TransferItem\TransferItemItem;
use App\Traits\Model\Inventory\InventoryTransferItemJoin;
use App\Helpers\Inventory\InventoryHelper;

class TransferItem extends TransactionModel
{
    use InventoryTransferItemJoin;
    
    public static $morphName = 'TransferItem';

    protected $connection = 'tenant';

    public static $alias = 'transfer_sent';

    public $timestamps = false;

    public $defaultNumberPrefix = 'TISEND';

    protected $fillable = [
        'warehouse_id',
        'to_warehouse_id',
        'driver'
    ];

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function to_warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(TransferItemItem::class);
    }

    // Relation that not archived and not canceled
    public function receiveItem()
    {
        return $this->hasMany(ReceiveItem::class)->join(Form::getTableName(), function ($q) {
            $q->on(Form::getTableName('formable_id'), '=', ReceiveItem::getTableName('id'))
                ->where(Form::getTableName('formable_type'), ReceiveItem::$morphName);
        })->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            })->select(ReceiveItem::getTableName('*'));
    }

    public function isAllowedToUpdate()
    {
        // Check if not referenced by purchase order
        if ($this->receiveItem->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by purchase receive', $this->receiveItem);
        }
    }

    public function isAllowedToDelete()
    {
        // Check if not referenced by purchase order
        if ($this->receiveItem->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by purchase receive', $this->ReceiveItems);
        }

    }

    public static function create($data)
    {
        $transferItem = new self;
        $transferItem->fill($data);

        $items = self::mapItems($data['items'] ?? []);

        $transferItem->save();

        $transferItem->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $transferItem);

        return $transferItem;
    }

    private static function mapItems($items)
    {
        $array = [];
        foreach ($items as $item) {
            $itemModel = Item::find($item['item_id']);
            if ($itemModel->require_production_number || $itemModel->require_expiry_date) {
                if ($item['dna']) {
                    foreach ($item['dna'] as $dna) {
                        if ($dna['quantity'] > 0) {
                            $dnaItem = $item;
                            $dnaItem['quantity'] = $dna['quantity'];
                            $dnaItem['production_number'] = $dna['production_number'];
                            $dnaItem['expiry_date'] = $dna['expiry_date'];
                            $dnaItem['stock'] = $dna['remaining'];
                            $dnaItem['balance'] = $dna['remaining'] - $dna['quantity'];
                            array_push($array, $dnaItem);
                        }
                    }
                }
            } else {
                array_push($array, $item);
            }
        }
        
        return array_map(function ($item) {
            $transferItemItem = new TransferItemItem;
            $transferItemItem->fill($item);

            return $transferItemItem;
        }, $array);
    }

    /**
     * Update price, cogs in inventory.
     *
     * @param $form
     * @param $transferItem
     */
    public static function updateInventory($form, $transferItem)
    {
        foreach ($transferItem->items as $item) {
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
                InventoryHelper::decrease($form, $item->TransferItem->warehouse, $item->item, $item->quantity, $item->unit, $item->converter, $options);

                $distributionWarehouse = Warehouse::where('name', 'DISTRIBUTION WAREHOUSE')->first();
                InventoryHelper::increase($form, $distributionWarehouse, $item->item, $item->quantity, $item->unit, $item->converter, $options);
            }
        }
    }

    public static function updateJournal($transferItem)
    {
        /**
         * Journal Table
         * -----------------------------------------------------
         * Account                            | Debit | Credit |
         * -----------------------------------------------------
         * 1. Inventory in distribution       |   v   |        | 
         * 2. Inventories                     |       |   v    | Master Item
         */
        foreach ($transferItem->items as $transferItemItem) {
            $itemAmount = $transferItemItem->item->cogs($transferItemItem->item_id) * $transferItemItem->quantity;

            // 1. Inventory in distribution
            $journal = new Journal;
            $journal->form_id = $transferItem->form->id;
            $journal->journalable_type = Item::$morphName;
            $journal->journalable_id = $transferItemItem->item_id;
            $journal->chart_of_account_id = get_setting_journal('transfer item', 'inventory in distribution');
            $journal->debit = $itemAmount;
            $journal->save();

            // 2. Inventories
            $journal = new Journal;
            $journal->form_id = $transferItem->form->id;
            $journal->journalable_type = Item::$morphName;
            $journal->journalable_id = $transferItemItem->item_id;
            $journal->chart_of_account_id = $transferItemItem->item->chart_of_account_id;
            $journal->credit = $itemAmount;
            $journal->save();
        }
    }
}
