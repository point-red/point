<?php

namespace App\Traits;

use App\Model\Form;

trait FormScopes
{
    public function scopeJoinForm($query, $callerClass = null)
    {
        $callerClass = $callerClass ?? get_class($this);
        $query->join(Form::getTableName(), Form::getTableName('formable_id'), '=', $callerClass::getTableName('id'))
            ->where(Form::getTableName('formable_type'), $callerClass);
    }

    public function scopeDone($query)
    {
        $query->where(Form::getTableName('done'), true);
    }

    public function scopeOrDone($query)
    {
        $query->orWhere(Form::getTableName('done'), true);
    }

    public function scopeNotDone($query)
    {
        $query->where(Form::getTableName('done'), false);
    }

    public function scopeOrNotDone($query)
    {
        $query->orWhere(Form::getTableName('done'), false);
    }

    public function scopeApprovalApproved($query)
    {
        $query->where(Form::getTableName('approved'), true);
    }

    public function scopeOrApprovalApproved($query)
    {
        $query->orWhere(Form::getTableName('approved'), true);
    }

    public function scopeApprovalRejected($query)
    {
        $query->where(Form::getTableName('approved'), false);
    }

    public function scopeOrApprovalRejected($query)
    {
        $query->orWhere(Form::getTableName('approved'), false);
    }

    public function scopeApprovalPending($query)
    {
        $query->whereNull(Form::getTableName('approved'));
    }

    public function scopeOrApprovalPending($query)
    {
        $query->orWhereNull(Form::getTableName('approved'));
    }

    public function scopeNotRejected($query)
    {
        $query->approvalPending()->orApprovalApproved();
    }

    public function scopeCancellationApproved($query)
    {
        $query->where(Form::getTableName('canceled'), true);
    }

    public function scopeOrCancellationApproved($query)
    {
        $query->orWhere(Form::getTableName('canceled'), true);
    }

    public function scopeCancellationRejected($query)
    {
        $query->where(Form::getTableName('canceled'), false);
    }

    public function scopeOrCancellationRejected($query)
    {
        $query->orWhere(Form::getTableName('canceled'), false);
    }

    public function scopeCancellationPending($query)
    {
        $query->whereNull(Form::getTableName('canceled'));
    }

    public function scopeOrCancellationPending($query)
    {
        $query->orWhereNull(Form::getTableName('canceled'));
    }

    public function scopeNotCanceled($query)
    {
        $query->cancellationPending()->orCancellationRejected();
    }

    public function scopeNotArchived($query)
    {
        $query->whereNotNull(Form::getTableName('number'));
    }

    public function scopeArchived($query)
    {
        $query->whereNull(Form::getTableName('number'));
    }

    public function scopeActive($query)
    {
        $query->notCanceled()->notRejected()->notArchived();
    }
}
