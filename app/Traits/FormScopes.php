<?php

namespace App\Traits;

trait FormScopes
{
    public function scopeDone($query)
    {
        $query->whereHas('form', function($q) {
            $q->where('done', true);
        });
    }


    public function scopeNotDone($query)
    {
        $query->whereHas('form', function($q) {
            $q->where('done', false);
        });
    }


    public function scopeApproved($query)
    {
        $query->whereHas('form', function($q) {
            $q->where('approved', true);
        });
    }


    public function scopeApprovalRejected($query)
    {
        $query->whereHas('form', function($q) {
            $q->where('approved', false);
        });
    }


    public function scopeApprovalPending($query)
    {
        $query->whereHas('form', function($q) {
            $q->whereNull('approved');
        });
    }


    public function scopeNotRejected($query)
    {
        $query->whereHas('form', function($q) {
            $q->whereNull('approved');
            $q->orWhere('approved', true);
        });
    }

    public function scopeCancellationApproved($query)
    {
        $query->whereHas('form', function($q) {
            $q->where('canceled', true);
        });
    }


    public function scopeCancellationRejected($query)
    {
        $query->whereHas('form', function($q) {
            $q->where('canceled', false);
        });
    }


    public function scopeCancellationPending($query)
    {
        $query->whereHas('form', function($q) {
            $q->whereNull('canceled');
        });
    }


    public function scopeNotCanceled($query)
    {
        $query->whereHas('form', function($q) {
            $q->whereNull('canceled');
            $q->orWhere('canceled', true);
        });
    }

    public function scopeNotArchived($query)
    {
        $query->whereHas('form', function($q) {
            $q->whereNotNull('number');
        });
    }

    public function scopeArchived($query)
    {
        $query->whereHas('form', function($q) {
            $q->whereNull('number');
        });
    }

    public function scopeActive($query)
    {
        $query->notCanceled()->notRejected()->notArchived();
    }

    public function scopeActivePending($query)
    {
        $query->active()->notDone();
    }

    public function scopeActiveDone($query)
    {
        $query->active()->done();
    }
}
