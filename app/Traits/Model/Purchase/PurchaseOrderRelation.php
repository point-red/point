<?php

namespace App\Traits\Model\Purchase;


use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrderItem;
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

    // Select relation that not archived and not canceled
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
