<?php

namespace App\Model\Finance\Payment;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Allocation;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\TransactionModel;

class PaymentDetail extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'payment_detail';

    public $timestamps = false;

    protected $fillable = [
        'chart_of_account_id',
        'referenceable_type',
        'referenceable_id',
        'allocation_id',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public static function referenceableIsValid($value)
    {
        $referenceableTypes = [
            SalesInvoice::$morphName,
            PurchaseInvoice::$morphName,
            SalesDownPayment::$morphName,
            PurchaseDownPayment::$morphName,
        ];

        return in_array($value, $referenceableTypes);
    }

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

    public function isDownPayment()
    {
        $morphNames = [
            SalesDownPayment::$morphName,
            PurchaseDownPayment::$morphName,
        ];

        return in_array($this->referenceable_type, $morphNames);
    }
}
