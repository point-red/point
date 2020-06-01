<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseReceive\PurchaseReceiveItem;

trait PurchaseReceiveRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PurchaseReceiveItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
