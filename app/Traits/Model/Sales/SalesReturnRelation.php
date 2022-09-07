<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesReturn\SalesReturnItem;
use App\Model\Sales\SalesReturn\SalesReturnService;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Master\Warehouse;

trait SalesReturnRelation
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
        return $this->hasMany(SalesReturnItem::class);
    }

    public function services()
    {
        return $this->hasMany(SalesReturnService::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function paymentCollections()
    {
        return $this->morphToMany(PaymentCollection::class, 'referenceable', 'sales_payment_collection_details', null,'sales_payment_collection_id')
            ->join(Form::getTableName(), function ($q) {
                $q->on(Form::getTableName('formable_id'), '=', PaymentCollection::getTableName('id'))
                    ->where(Form::getTableName('formable_type'), PaymentCollection::$morphName);
            })
            ->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            });
    }

}
