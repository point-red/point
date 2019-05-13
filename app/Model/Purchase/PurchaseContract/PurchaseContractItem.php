<?php

namespace App\Model\Purchase\PurchaseContract;

use App\Model\TransactionModel;
use App\Model\Purchase\PurchaseOrder\PurchaseOrderItem;

class PurchaseContractItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'item_unit_id',
        'item_name',
        'price',
        'quantity',
        'unit',
        'converter',
        'notes',
        'allocation_id',
    ];

    protected $casts = [
        'price' => 'double',
        'quantity' => 'double',
        'converter' => 'double',
    ];

    public function purchaseContract()
    {
        return $this->belongsTo(PurchaseContract::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class)
            ->whereHas('purchaseOrder', function ($query) {
                $query->active();
            });
    }
}
