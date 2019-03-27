<?php

namespace App\Model\Inventory\Transfer;

use App\Model\Master\Item;
use App\Model\TransactionModel;

class TransferItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'transfer_id',
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
