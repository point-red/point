<?php

namespace App\Model\Purchase\PurchasePaymentOrder;

use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\TransactionModel;

class PurchasePaymentOrderDownPayment extends TransactionModel
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

    public function downpayment()
    {
        return $this->belongsTo(PurchaseDownPayment::class);
    }
}
