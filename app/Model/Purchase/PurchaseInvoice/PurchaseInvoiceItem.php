<?php

namespace App\Model\Purchase\PurchaseInvoice;

use App\Model\Master\Item;
use App\Model\TransactionModel;
use App\Model\Master\Allocation;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;

class PurchaseInvoiceItem extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'purchase_receive_id',
        'purchase_receive_item_id',
        'item_id',
        'item_name',
        'quantity',
        'unit',
        'converter',
        'price',
        'discount_percent',
        'discount_value',
        'taxable',
        'notes',
        'allocation_id',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function purchaseReceive()
    {
        return $this->belongsTo(PurchaseReceive::class);
    }
}
