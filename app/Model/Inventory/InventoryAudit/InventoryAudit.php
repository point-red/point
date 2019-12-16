<?php

namespace App\Model\Inventory\InventoryAudit;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class InventoryAudit extends TransactionModel
{
    public static $morphName = 'InventoryAudit';

    protected $connection = 'tenant';

    public $timestamps = false;

    public $defaultNumberPrefix = 'IA';

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
            $inventoryAuditItem = new InventoryAuditItem;
            $inventoryAuditItem->fill($item);

            array_push($inventoryAuditItems, $inventoryAuditItem);
        }

        $inventoryAudit->items()->saveMany($inventoryAuditItems);

        self::updateStock($inventoryAudit);

        return $inventoryAudit;
    }

    private static function updateStock($inventoryAudit)
    {
        foreach ($inventoryAudit->items as $inventoryAuditItem) {
            InventoryHelper::audit($inventoryAudit->form->id,
                $inventoryAudit->warehouse_id,
                $inventoryAuditItem->item_id,
                $inventoryAuditItem->quantity,
                $inventoryAuditItem->price,
                [
                    'production_number' => $inventoryAuditItem->production_number,
                    'expiry_date' => $inventoryAuditItem->expiry_date
                ]);
        }
    }
}
