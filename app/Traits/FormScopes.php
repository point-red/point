<?php

namespace App\Traits;

trait FormScopes
{
    public function scopeJoinForm($query)
    {
        $query->join('forms', 'forms.id', '=', $this->table . '.form_id')
            ->join('form_approvals', 'form_approvals.id', '=', 'forms.form_id')
            ->join('form_cancellations', 'form_cancellations.id', '=', 'forms.form_id');
    }

    public function scopeIsDone($query)
    {
        $query->where('forms.done', true);
    }

    public function scopeIsPending($query)
    {
        $query->where('forms.done', false);
    }

    public function scopeIsApprovalApproved($query)
    {
        $query->where('forms.approved', true);
    }

    public function scopeIsApprovalRejected($query)
    {
        $query->where('forms.approved', false);
    }

    public function scopeIsApprovalPending($query)
    {
        $query->where('forms.approved', null);
    }

    public function scopeIsCancellationApproved($query)
    {
        $query->where('forms.cancellation', true);
    }

    public function scopeIsCancellationRejected($query)
    {
        $query->where('forms.cancellation', false);
    }

    public function scopeIsCancellationPending($query)
    {
        $query->where('forms.cancellation', null);
    }

    public function scopeIsActive($query)
    {
        $query->whereNotNull('forms.number');
    }

    public function scopeIsNotActive($query)
    {
        $query->whereNull('forms.number');
    }
}
