<?php

namespace App\Traits\Model\Finance;

use App\Model\HumanResource\Employee\Employee;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\CashAdvance\CashAdvanceDetail;
use App\Model\Form;

trait CashAdvanceRelation
{
    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function details()
    {
        return $this->hasMany(CashAdvanceDetail::class);
    }

}
