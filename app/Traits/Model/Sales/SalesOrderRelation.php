<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\SalesContract\SalesContract;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\SalesOrder\SalesOrderItem;
use App\Model\Sales\SalesQuotation\SalesQuotation;

trait SalesOrderRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesQuotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class)->active();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function downPayments()
    {
        return $this->morphMany(SalesDownPayment::class, 'downpaymentable')->active();
    }

    public function paidDownPayments()
    {
        return $this->downPayments()->whereNotNull('paid_by');
    }

    public function remainingDownPayments()
    {
        return $this->paidDownPayments()->where('remaining', '>', 0);
    }

    public function salesContract()
    {
        return $this->belongsTo(SalesContract::class);
    }
}
