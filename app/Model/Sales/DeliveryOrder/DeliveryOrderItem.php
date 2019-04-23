<?php

namespace App\Model\Sales\DeliveryOrder;

use App\Model\Master\Item;
use App\Model\TransactionModel;
use App\Model\Master\Allocation;
use App\Model\Sales\DeliveryNote\DeliveryNoteItem;

class DeliveryOrderItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'sales_order_item_id',
        'item_id',
        'item_name',
        'quantity',
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
        'quantity' => 'double',
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
        return $this->hasMany(DeliveryNoteItem::class)
            ->whereHas('deliveryNote', function($query) {
                $query->active();
            });
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
