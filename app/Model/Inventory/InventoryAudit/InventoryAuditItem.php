<?php

namespace App\Model\Inventory\InventoryAudit;

use App\Model\Master\Item;
use App\Model\TransactionModel;

class InventoryAuditItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
    ];

    protected $fillable = [
        'item_id',
        'quantity',
        'unit',
        'notes',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
