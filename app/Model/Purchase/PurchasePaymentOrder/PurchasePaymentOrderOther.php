<?php

namespace App\Model\Purchase\PurchasePaymentOrder;

use App\Model\TransactionModel;

class PurchasePaymentOrderOther extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'chart_of_account_id',
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
