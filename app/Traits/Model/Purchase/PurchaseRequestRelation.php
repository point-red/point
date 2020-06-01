<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestItem;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestService;

trait PurchaseRequestRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function services()
    {
        return $this->hasMany(PurchaseRequestService::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Select relation that not archived and not canceled
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class)
            ->join(Form::getTableName(), function ($q) {
                $q->on(Form::getTableName('formable_id'), '=', PurchaseOrder::getTableName('id'))
                    ->where(Form::getTableName('formable_type'), PurchaseOrder::$morphName);
            })->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            });
    }
}
