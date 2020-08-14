<?php

namespace App\Traits\Model\Sales;

use App\Model\Finance\Payment\Payment;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesInvoice\SalesInvoice;

trait SalesDownPaymentRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
        return $this->morphToMany(Payment::class, 'referenceable', 'payment_details')
            ->join(Form::getTableName(), function ($q) {
                $q->on(Form::getTableName('formable_id'), '=', Payment::getTableName('id'))
                    ->where(Form::getTableName('formable_type'), Payment::$morphName);
            })
            ->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            });
    }

    public function invoices()
    {
        return $this->belongsToMany(SalesInvoice::class, 'purchase_down_payment_invoice', 'invoice_id', 'down_payment_id')
            ->join(Form::getTableName(), function ($q) {
                $q->on(Form::getTableName('formable_id'), '=', SalesInvoice::getTableName('id'))
                    ->where(Form::getTableName('formable_type'), SalesInvoice::$morphName);
            })
            ->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            });
    }
}
