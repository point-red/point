<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Model\Master\Allocation;
use App\Model\Master\Service;
use App\Model\TransactionModel;

class PurchaseRequestService extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'purchase_request_id',
        'service_id',
        'quantity',
        'price',
        'notes',
        'allocation_id',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
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
