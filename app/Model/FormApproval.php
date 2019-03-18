<?php

namespace App\Model;

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

    public function getApprovalAtAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setApprovalAtAttribute($value)
    {
        $this->attributes['approval_at'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }
}
