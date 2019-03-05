<?php

namespace App\Model;

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

    public function getExpiredAtAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
    }

    public function setExpiredAtAttribute($value)
    {
        $this->attributes['expired_at'] = Carbon::parse($value, request()->header('Timezone'))->timezone('UTC')->toDateTimeString();
    }

    public function getApprovalAtAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
    }

    public function setApprovalAtAttribute($value)
    {
        $this->attributes['approval_at'] = Carbon::parse($value, request()->header('Timezone'))->timezone('UTC')->toDateTimeString();
    }
}
