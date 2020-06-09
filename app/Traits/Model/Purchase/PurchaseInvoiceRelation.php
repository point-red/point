<?php

namespace App\Traits\Model\Purchase;

use App\Model\Finance\Payment\Payment;
use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoiceItem;

trait PurchaseInvoiceRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function downPayments()
    {
        return $this->belongsToMany(PurchaseDownPayment::class, 'down_payment_invoice', 'down_payment_id', 'invoice_id');
    }

    public function payments()
    {
        return $this->morphToMany(Payment::class, 'referenceable', 'payment_details')->active();
    }
}
