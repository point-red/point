<?php

namespace App\Traits\Model\Pos;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Pos\PosBillItem;

trait PosBillRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PosBillItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
