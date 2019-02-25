<?php

namespace App\Model\Purchase\PurchaseReceive;

use App\Model\Master\Item;
use App\Model\TransactionModel;
use App\Model\Master\Allocation;

class PurchaseReceiveItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'purchase_order_item_id',
        'item_id',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'quantity',
        'unit',
        'converter',
        'notes',
        'allocation_id',
        'price',
        'purchase_price',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
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

    // For TransactionController
    public function setPurchasePriceAttribute($value)
    {
        $this->attributes['price'] = $value;
    }
}
