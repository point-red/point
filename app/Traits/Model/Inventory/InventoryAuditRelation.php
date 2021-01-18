<?php

namespace App\Traits\Model\Inventory;

use App\Model\Form;
use App\Model\Inventory\InventoryAudit\InventoryAuditItem;
use App\Model\Master\Warehouse;

trait InventoryAuditRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(InventoryAuditItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
