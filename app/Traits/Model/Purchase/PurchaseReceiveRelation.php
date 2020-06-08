<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseReceive\PurchaseReceiveItem;

trait PurchaseReceiveRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReceiveItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function purchaseInvoices()
    {
        return $this->belongsToMany(PurchaseInvoice::class, 'purchase_invoice_items')
            ->join(Form::getTableName(), function ($q) {
                $q->on(Form::getTableName('formable_id'), '=', PurchaseReceive::getTableName('id'))
                    ->where(Form::getTableName('formable_type'), PurchaseReceive::$morphName);
            })
            ->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            });
    }
}
