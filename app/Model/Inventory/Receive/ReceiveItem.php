<?php

namespace App\Model\Inventory\Receive;

use App\Model\Master\Item;
use App\Model\TransactionModel;

class ReceiveItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'receive_id',
        'item_id',
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
