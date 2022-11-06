<?php

namespace App\Model\Finance\CashAdvance;

use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\Finance\Payment\Payment;
use App\Model\TransactionModel;

class CashAdvancePayment extends TransactionModel
{
    public static $morphName = 'CashAdvancePayment';

    protected $connection = 'tenant';

    public static $alias = 'cash_advance_payment';

    protected $table = 'cash_advance_payment';

    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'cash_advance_id',
    ];

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
