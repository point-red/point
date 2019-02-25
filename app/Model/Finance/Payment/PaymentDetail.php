<?php

namespace App\Model\Finance\Payment;

use App\Model\TransactionModel;
use App\Model\Master\Allocation;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;

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

    protected $referenceableType = [
        'sale_invoice' => SalesInvoice::class,
        'purchase_invoice' => PurchaseInvoice::class,
    ];

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

    public function setReferenceableTypeAttribute($value)
    {
        $this->attributes['referenceable_type'] = $this->referenceableType[$value];
    }
}
