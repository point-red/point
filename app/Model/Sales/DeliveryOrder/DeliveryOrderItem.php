<?php

namespace App\Model\Sales\DeliveryOrder;

use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\TransactionModel;

class DeliveryOrderItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'sales_order_item_id',
        'item_id',
        'quantity',
        'unit',
        'converter',
        'description'
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
