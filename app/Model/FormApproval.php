<?php

namespace App\Model;

use App\Model\Master\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FormApproval extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the form that owns the form approval.
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function requestedTo()
    {
        return $this->belongsTo(User::class, 'requested_to');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function getApprovalAtAttribute($value)
    {
        if (! $value) {
            return;
        }

        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setApprovalAtAttribute($value)
    {
        $this->attributes['approval_at'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public function approve()
    {
        $form = $this->form;

        if ($form->approved != null) {
            return false;
        }

        $this->approval_at = now();
        $this->save();

        $form->approved = true;
        $form->save();

        return true;
    }

    public function reject($reason)
    {
        $form = $this->form;

        if ($form->approved != null) {
            return false;
        }

        $this->approval_at = now();
        $this->reason = $reason;
        $this->save();

        $form->approved = false;
        $form->save();

        return true;
    }
}
