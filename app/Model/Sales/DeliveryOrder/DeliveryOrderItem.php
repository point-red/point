<?php

namespace App\Model\Sales\DeliveryOrder;

use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\Sales\DeliveryNote\DeliveryNoteItem;
use App\Model\TransactionModel;

class DeliveryOrderItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'sales_delivery_order_item';

    public $timestamps = false;

    protected $fillable = [
        'sales_order_item_id',
        'item_id',
        'item_name',
        'quantity_requested',
        'quantity_delivered',
        'quantity_remaining',
        'price',
        'discount_percent',
        'discount_value',
        'taxable',
        'unit',
        'converter',
        'notes',
        'allocation_id',
    ];

    protected $casts = [
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'quantity_requested' => 'double',
        'quantity_delivered' => 'double',
        'quantity_remaining' => 'double',
        'converter' => 'double',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function deliveryNoteItems()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
