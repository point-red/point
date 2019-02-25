<?php

namespace App\Model\Sales\DeliveryNote;

use App\Model\Master\Item;
use App\Model\TransactionModel;
use App\Model\Master\Allocation;

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
        'notes',
        'price',
        'discount_percent',
        'discount_value',
        'taxable',
        'allocation_id',
        'sell_price',
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

    // For TransactionController
    public function setSellPriceAttribute($value)
    {
        $this->attributes['price'] = $value;
    }
}
