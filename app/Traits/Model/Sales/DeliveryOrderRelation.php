<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\DeliveryOrder\DeliveryOrderItem;
use App\Model\Sales\SalesOrder\SalesOrder;

trait DeliveryOrderRelation
{
    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class)
            ->select(DeliveryNote::getTableName() . '.*')
            ->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', DeliveryNote::getTableName().'.id')
                    ->where(Form::$alias.'.formable_type', DeliveryNote::$morphName);
            })
            ->active();
    }

    /* Invoice needs DeliveryOrders that is done and has pendingDeliveryNotes*/
    public function pendingDeliveryNotes()
    {
        return $this->deliveryNotes()->notDone();
    }

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class)->active();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

}
