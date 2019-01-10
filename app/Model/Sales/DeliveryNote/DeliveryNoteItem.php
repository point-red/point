<?php

namespace App\Model\Sales\DeliveryNote;

use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\TransactionModel;

class DeliveryNoteItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'sales_order_item_id',
        'item_id',
        'quantity',
        'unit',
        'converter',
    ];

    protected $casts = [
        'quantity'  => 'double',
        'converter' => 'double',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
