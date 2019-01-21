<?php

namespace App\Model\Purchase\PurchasePaymentOrder;

use App\Model\TransactionModel;

class PurchasePaymentOrderCutOff extends TransactionModel
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
}
