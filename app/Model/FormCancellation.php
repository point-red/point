<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FormCancellation extends Model
{
    protected $connection = 'tenant';

    /**
     * Get the form that owns the form cancellation.
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function getExpiredAtAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setExpiredAtAttribute($value)
    {
        $this->attributes['expired_at'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public function getApprovalAtAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setApprovalAtAttribute($value)
    {
        $this->attributes['approval_at'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public function approve()
    {
        $form = $this->form;

        if ($form->canceled != null) {
            return false;
        }

        $this->approval_at = now();
        $this->save();

        $form->canceled = true;
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

        $form->canceled = false;
        $form->save();

        return true;
    }
}
