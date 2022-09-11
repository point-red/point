<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\DeliveryNote\DeliveryNoteItem;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\SalesInvoice\SalesInvoice;

trait DeliveryNoteRelation
{
    /* Invoice needs DeliveryNotes that is done and has pendingDeliveryNotes*/
    public function pendingDeliveryNotes()
    {
        return $this->deliveryNotes()->notDone();
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(deliveryOrder::class, 'delivery_order_id');
    }

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class)->active();
    }

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class, 'referenceable_id')
            ->select(SalesInvoice::getTableName().'.*')
            ->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', SalesInvoice::getTableName().'.id')
                    ->where(Form::$alias.'.formable_type', SalesInvoice::$morphName);
            })
            ->active();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
