<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Model\Master\Item;
use App\Model\TransactionModel;
use App\Model\Master\Allocation;
use App\Model\Purchase\PurchaseOrder\PurchaseOrderItem;

class PurchaseRequestItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_name',
        'quantity',
        'unit',
        'converter',
        'price',
        'notes',
        'allocation_id',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
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

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class)
            ->whereHas('purchaseOrder', function($query) {
                $query->active();
            });
    }
}
