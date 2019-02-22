<?php

namespace App\Model\Inventory\InventoryAudit;

use App\Model\Form;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;
use Illuminate\Http\Request;

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

    public function create(Request $request)
    {
        $inventoryAudit = new InventoryAudit;
        $inventoryAudit->warehouse_id = $request->get('warehouse_id');
        $inventoryAudit->save();

        $form = new Form;
        $form->fillData($request, $inventoryAudit);

        $items = $request->get('items');
        $inventoryAuditItems = [];
        foreach ($items as $key => $item) {
            // TODO validation $item
            $inventoryAuditItem = new InventoryAuditItem;
            $inventoryAuditItem->fill($item);

            array_push($inventoryAuditItems, $inventoryAuditItem);
        }

        $inventoryAudit->items()->createMany($inventoryAuditItems);

        return $inventoryAudit->load('form');
    }
}
