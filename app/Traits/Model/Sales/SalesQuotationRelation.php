<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesQuotation\SalesQuotationItem;

trait SalesQuotationRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(SalesQuotationItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
