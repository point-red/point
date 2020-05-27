<?php

namespace App\Traits\Model\Finance;


use App\Model\Finance\Payment\Payment;
use App\Model\Finance\PaymentOrder\PaymentOrderDetail;
use App\Model\Form;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestItem;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestService;

trait PaymentOrderRelation
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

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function details()
    {
        return $this->hasMany(PaymentOrderDetail::class);
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
}
