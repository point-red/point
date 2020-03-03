<?php

namespace App\Traits;

use App\Model\Form;

trait FormScopes
{
    // Form don't need another follow up or already completed by another form
    public function scopeDone($query)
    {
        $query->whereHas('form', function ($q) {
            $q->where('done', true);
        });
    }

    // Form waiting to be completed by another form
    public function scopePending($query)
    {
        $query->whereHas('form', function ($q) {
            $q->where('done', false);
        });
    }

    // Form approval approved (inventory and journal is posted)
    public function scopeApprovalApproved($query)
    {
        $query->whereHas('form', function ($q) {
            $q->where('approval_status', 1);
        });
    }

    // Form approval rejected and need revision
    public function scopeApprovalRejected($query)
    {
        $query->whereHas('form', function ($q) {
            $q->where('approval_status', -1);
        });
    }

    // Form approval pending (inventory and journal is not posted yet until approved)
    public function scopeApprovalPending($query)
    {
        $query->whereHas('form', function ($q) {
            $q->where('approval_status', 0);
        });
    }

    public function scopeCancellationApproved($query)
    {
        $query->whereHas('form', function ($q) {
            $q->where('cancellation_status', 1);
        });
    }

    public function scopeCancellationRejected($query)
    {
        $query->whereHas('form', function ($q) {
            $q->where('cancellation_status', -1);
        });
    }

    public function scopeCancellationPending($query)
    {
        $query->whereHas('form', function ($q) {
            $q->where('cancellation_status', 0);
        });
    }

    public function scopeNotCanceled($query)
    {
        $query->whereHas('form', function ($q) {
            $q->whereNull('cancellation_status')->orWhere('cancellation_status', '!=', '1');
        });
    }

    public function scopeNotArchived($query)
    {
        $query->whereHas('form', function ($q) {
            $q->whereNotNull('number');
        });
    }

    public function scopeArchived($query)
    {
        $query->whereHas('form', function ($q) {
            $q->whereNull('number');
        });
    }

    public function scopeActive($query)
    {
        $query->notCanceled()->notArchived();
    }

    public function scopeActivePending($query)
    {
        $query->active()->pending();
    }

    public function scopeActiveDone($query)
    {
        $query->active()->done();
    }

    public function scopeJoinForm($query)
    {
        $caller = get_class($this);
        $query->join(Form::getTableName(), function ($q) use ($caller) {
            $q->on(Form::getTableName('formable_id'), '=', $caller::getTableName('id'))
                ->where(Form::getTableName('formable_type'), $caller::$morphName);
        });
    }
}
