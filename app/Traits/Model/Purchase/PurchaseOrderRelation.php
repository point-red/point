<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\Purchase\PurchaseOrder\PurchaseOrderItem;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;

trait PurchaseOrderRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function paidDownPayments()
    {
        return $this->downPayments()->whereNotNull('paid_by');
    }

    public function remainingDownPayments()
    {
        return $this->paidDownPayments()->where('remaining', '>', 0);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Relation that not archived and not canceled
    public function purchaseReceives()
    {
        return $this->hasMany(PurchaseReceive::class)->join(Form::getTableName(), function ($q) {
            $q->on(Form::getTableName('formable_id'), '=', PurchaseReceive::getTableName('id'))
                ->where(Form::getTableName('formable_type'), PurchaseReceive::$morphName);
        })->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            });
    }

    // Relation that not archived and not canceled
    public function downPayments()
    {
        return $this->morphMany(PurchaseDownPayment::class, 'downpaymentable')->join(Form::getTableName(), function ($q) {
            $q->on(Form::getTableName('formable_id'), '=', PurchaseDownPayment::getTableName('id'))
                ->where(Form::getTableName('formable_type'), PurchaseDownPayment::$morphName);
        })->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            });
    }

    // Relation that not archived and not canceled
    public function deliverOrders()
    {
        return $this->hasMany(DeliveryOrder::class)
            ->join(Form::getTableName(), function ($q) {
                $q->on(Form::getTableName('formable_id'), '=', DeliveryOrder::getTableName('id'))
                    ->where(Form::getTableName('formable_type'), DeliveryOrder::$morphName);
            })->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            });
    }
}
