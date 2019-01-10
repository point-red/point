<?php

namespace App\Model\Purchase\PurchaseInvoice;

use App\Model\Master\Allocation;
use App\Model\Master\Item;
use Illuminate\Database\Eloquent\Model;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;

class PurchaseInvoiceItem extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $casts = [
        'quantity'  => 'double',
        'converter' => 'double',
        'price' => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double'
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
