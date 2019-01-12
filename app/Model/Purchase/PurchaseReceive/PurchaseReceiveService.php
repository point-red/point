<?php

namespace App\Model\Purchase\PurchaseReceive;

use App\Model\Master\Allocation;
use App\Model\Master\Service;
use App\Model\TransactionModel;

class PurchaseReceiveService extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'purchase_order_item_id',
        'service_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'double',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
