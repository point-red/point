<?php

namespace App\Model\Purchase\PurchaseInvoice;

use App\Model\Master\Allocation;
use App\Model\Master\Service;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceService extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $casts = [
        'quantity'  => 'double',
        'price' => 'double',
        'discount_value' => 'double',
        'discount_percent' => 'double'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
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
