<?php

namespace App\Model\Inventory\InventoryAudit;

use App\Model\Master\Item;
use App\Model\TransactionModel;

class InventoryAuditItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'inventory_audit_item';

    public $timestamps = false;

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
        'price' => 'double',
    ];

    protected $fillable = [
        'item_id',
        'quantity',
        'expiry_date',
        'production_number',
        'price',
        'unit',
        'notes',
        'converter',
    ];

    public function setExpiryDateAttribute($value)
    {
        $this->attributes['expiry_date'] = convert_to_server_timezone($value);
    }

    public function getExpiryDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
