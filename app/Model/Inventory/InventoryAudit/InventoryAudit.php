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

    public function create()
    {
        // TODO: save inventory audit
    }
}
