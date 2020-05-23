<?php

namespace App\Traits;

use App\Model\Form;

trait FormScopes
{
    // Form don't need another follow up or already completed by another form
    public function scopeDone($query)
    {
        $query->where(Form::$alias . '.done', true);
    }

    // Form waiting to be completed by another form
    public function scopePending($query)
    {
        $query->where(Form::$alias . '.done', false);
    }

    // Form approval approved (inventory and journal is posted)
    public function scopeApprovalApproved($query)
    {
        $query->where(Form::$alias . '.approval_status', 1);
    }

    // Form approval rejected and need revision
    public function scopeApprovalRejected($query)
    {
        $query->where(Form::$alias . '.approval_status', -1);
    }

    // Form approval pending (inventory and journal is not posted yet until approved)
    public function scopeApprovalPending($query)
    {
        $query->where(Form::$alias . '.approval_status', 0);
    }

    public function scopeCancellationApproved($query)
    {
        $query->where(Form::$alias . '.cancellation_status', 1);
    }

    public function scopeCancellationRejected($query)
    {
        $query->where(Form::$alias . '.cancellation_status', -1);
    }

    public function scopeCancellationPending($query)
    {
        $query->where(Form::$alias . '.cancellation_status', 0);
    }

    public function scopeNotCanceled($query)
    {
        $query->whereNull(Form::$alias . '.cancellation_status')
            ->orWhere(Form::$alias . 'cancellation_status', '!=', '1');
    }

    public function scopeNotArchived($query)
    {
        $query->whereNotNull(Form::$alias . '.number');
    }

    public function scopeArchived($query)
    {
        $query->whereNull(Form::$alias . '.number');
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
            $q->on(Form::$alias . 'formable_id', '=', $caller::$alias . '.id')
                ->where(Form::$alias . 'formable_type', $caller::$morphName);
        });
    }
}
