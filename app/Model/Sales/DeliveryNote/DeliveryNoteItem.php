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
        'item_id',
        'delivery_order_item_id',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'quantity',
        'unit',
        'converter',
        'description',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double'
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
