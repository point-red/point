<?php

namespace App\Model\Inventory\InventoryAudit;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class InventoryAudit extends TransactionModel
{
    public static $morphName = 'InventoryAudit';

    protected $connection = 'tenant';

    public $timestamps = false;

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(InventoryAuditItem::class);
    }

    public static function create($data)
    {
        $inventoryAudit = new self;
        $inventoryAudit->warehouse_id = $data['warehouse_id'];
        $inventoryAudit->save();

        $form = new Form;
        $form->saveData($data, $inventoryAudit);

        $items = $data['items'];
        $inventoryAuditItems = [];
        foreach ($items as $key => $item) {
            // TODO validation $item
            $inventoryAuditItem = new InventoryAuditItem;
            $inventoryAuditItem->fill($item);

            array_push($inventoryAuditItems, $inventoryAuditItem);
        }

        $inventoryAudit->items()->saveMany($inventoryAuditItems);

        self::updateStock($data);

        return $inventoryAudit;
    }

    private static function updateStock($data)
    {
        $itemIds = array_column($data['items'], 'item_id');
        $items = Item::whereIn('id', $itemIds)->get();

        foreach ($items as $key => $item) {
            $item->stock = $data['items'][$key]['quantity'];
            $item->save();
        }
    }
}
