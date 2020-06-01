<?php

namespace App\Traits\Model\Purchase;

use App\Model\Finance\Payment\Payment;
use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;

trait PurchaseDownPaymentRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get all of the owning downpaymentable models.
     */
    public function downpaymentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the invoice's payment.
     */
    public function payments()
    {
        return $this->morphToMany(Payment::class, 'referenceable', 'payment_details')->active();
    }

    public function invoices()
    {
        return $this->belongsToMany(PurchaseInvoice::class, 'purchase_down_payment_invoice', 'invoice_id', 'down_payment_id')->active();
    }
}
