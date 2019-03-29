<?php

namespace App\Model\Inventory\TransferSendItem;

use App\Model\Master\Item;
use App\Model\TransactionModel;

class TransferSendItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'transfer_id',
        'item_id',
        'item_name',
        'quantity',
        'unit',
        'converter',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
