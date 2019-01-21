<?php

namespace App\Model\Purchase\PurchasePaymentOrder;

use App\Model\Purchase\PurchaseReturn\PurchaseReturn;
use App\Model\TransactionModel;

class PurchasePaymentOrderReturn extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function paymentOrder()
    {
        return $this->belongsTo(PurchasePaymentOrder::class);
    }

    public function return()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }
}
