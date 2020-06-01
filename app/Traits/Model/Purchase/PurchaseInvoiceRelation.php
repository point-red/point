<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoiceItem;

trait PurchaseInvoiceRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
