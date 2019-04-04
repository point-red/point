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
        if (!$value) {
            return null;
        }
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setApprovalAtAttribute($value)
    {
        $this->attributes['approval_at'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public static function create($formId, $requestedTo)
    {
        $formApproval = new FormApproval;
        $formApproval->form_id = $formId;
        $formApproval->requested_at = now();
        $formApproval->requested_by = auth()->user()->id;
        $formApproval->requested_to = $requestedTo;
        $formApproval->expired_at = date('Y-m-d H:i:s', strtotime('+7 days'));
        $formApproval->token = substr(md5(now()), 0, 24);
        $formApproval->save();
    }

    public static function approve($formId, $token, $reason = '')
    {
        $form = Form::findOrFail($formId);

        if ($form->approved != null) {
            return false;
        }

        $formApproval = FormApproval::where('form_id', $formId)->where('token', $token)->first();
        $formApproval->approval_at = now();
        $formApproval->approved = true;
        $formApproval->reason = $reason;
        $formApproval->save();

        $form->approved = true;
        $form->save();

        return true;
    }

    public static function reject($formId, $token, $reason = '')
    {
        $form = Form::findOrFail($formId);

        if ($form->approved != null) {
            return false;
        }

        $formApproval = FormApproval::where('form_id', $formId)->where('token', $token)->first();
        $formApproval->approval_at = now();
        $formApproval->approved = false;
        $formApproval->reason = $reason;
        $formApproval->save();

        $form->approved = false;
        $form->save();

        return true;
    }
}
