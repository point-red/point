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

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'cash_advance_payment')->withPivot(['archived_at']);
    }

    public function details()
    {
        return $this->hasMany(CashAdvanceDetail::class);
    }

}
