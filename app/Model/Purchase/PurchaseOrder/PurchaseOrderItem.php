<?php

namespace App\Model\Purchase\PurchaseOrder;

use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\Purchase\PurchaseReceive\PurchaseReceiveItem;
use App\Model\TransactionModel;

class PurchaseOrderItem extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'purchase_order_item';

    public $timestamps = false;

    protected $fillable = [
        'purchase_request_item_id',
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

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseReceiveItems()
    {
        return $this->hasMany(PurchaseReceiveItem::class)
            ->whereHas('purchaseReceive', function ($query) {
                $query->active();
            });
    }
}
