<?php

namespace App\Traits\Model\Finance;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\Finance\CashAdvance\CashAdvancePayment;
use App\Model\Finance\Payment\PaymentDetail;
use App\Model\Form;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;

trait PaymentRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    /**
     * Get all of the owning paymentable models.
     */
    public function paymentable()
    {
        return $this->morphTo();
    }

    public function paymentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'payment_account_id');
    }

    public function details()
    {
        return $this->hasMany(PaymentDetail::class, 'payment_id');
    }

    // Select relation that not archived and not canceled
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class)
            ->join(Form::getTableName(), function ($q) {
                $q->on(Form::getTableName('formable_id'), '=', PurchaseOrder::getTableName('id'))
                    ->where(Form::getTableName('formable_type'), PurchaseOrder::$morphName);
            })->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            });
    }

    /**
     * Get all related cash advances
     */
    public function cashAdvances()
    {
        return $this->belongsToMany(CashAdvance::class, 'cash_advance_payment', 'payment_id', 'cash_advance_id');
    }

    public function cashAdvance()
    {
        return $this->hasOne(CashAdvancePayment::class);
    }
}
