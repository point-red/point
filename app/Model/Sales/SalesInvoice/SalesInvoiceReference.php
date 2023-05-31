<?php

namespace App\Model\Sales\SalesInvoice;

use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\TransactionModel;

class SalesInvoiceReference extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'sales_invoice_reference';

    public $timestamps = false;

    protected $fillable = [
        'sales_invoice_id',
        'referenceable_type',
        'referenceable_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public static function referenceableIsValid($value)
    {
        $referenceableTypes = [
            SalesReturn::$morphName,
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

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }
}
