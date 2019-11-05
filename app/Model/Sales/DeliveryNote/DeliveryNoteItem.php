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
        'delivery_order_item_id',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'quantity',
        'unit',
        'converter',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'gross_weight' => 'double',
        'tare_weight' => 'double',
        'net_weight' => 'double',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
