<?php

namespace App\Model\Inventory\TransferItem;

use App\Model\Master\Item;
use App\Model\Inventory\TransferItem\TransferItem;
use App\Model\TransactionModel;

class TransferItemItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'transfer_sent_item';

    public $timestamps = false;

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
        'stock' => 'double',
        'balance' => 'double',
    ];

    protected $fillable = [
        'item_id',
        'item_name',
        'quantity',
        'converter',
        'expiry_date',
        'production_number',
        'unit',
        'notes',
        'stock',
        'balance'
    ];

    public function setExpiryDateAttribute($value)
    {
        $this->attributes['expiry_date'] = empty($value) ? null : convert_to_server_timezone($value);
    }

    public function getExpiryDateAttribute($value)
    {
        return empty($value) ? null : convert_to_local_timezone($value);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function TransferItem()
    {
        return $this->belongsTo(TransferItem::class);
    }
}
