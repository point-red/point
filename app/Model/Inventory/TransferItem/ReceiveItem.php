<?php

namespace App\Model\Inventory\TransferItem;

use App\Model\Form;
use App\Exceptions\IsReferencedException;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Master\Item;
use App\Model\Inventory\TransferItem\TransferItemItem;
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
        'transfer_item_id',
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

    public function from_warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(ReceiveItemItem::class);
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
}
