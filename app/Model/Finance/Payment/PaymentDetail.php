<?php

namespace App\Model\Finance\Payment;

use App\Model\TransactionModel;
use App\Model\Master\Allocation;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;

class PaymentDetail extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'chart_of_account_id',
        'allocation_id',
        'amount',
        'notes',
        'referenceable_type',
        'referenceable_id',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    // TODO validation referenceableType 
    // protected $referenceableType = [
    //     'SalesInvoice',
    //     'PurchaseInvoice',
    //     'SalesDownPayment',
    //     'PurchaseDownPayment',
    // ];

    /**
     * Get all of the owning referenceable models.
     */
    public function referenceable()
    {
        return $this->morphTo();
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}
