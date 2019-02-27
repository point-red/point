<?php

namespace App\Model\Inventory\InventoryAudit;

use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class InventoryAudit extends TransactionModel
{
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

    public function create($data)
    {
        $inventoryAudit = new InventoryAudit;
        $inventoryAudit->warehouse_id = $data['warehouse_id'];
        $inventoryAudit->save();

        $form = new Form;
        $form->fillData($data, $inventoryAudit);

        $items = $data['items'];
        $inventoryAuditItems = [];
        foreach ($items as $key => $item) {
            // TODO validation $item
            $inventoryAuditItem = new InventoryAuditItem;
            $inventoryAuditItem->fill($item);

            array_push($inventoryAuditItems, $inventoryAuditItem);
        }

        $inventoryAudit->items()->saveMany($inventoryAuditItems);

        return $inventoryAudit;
    }
}
