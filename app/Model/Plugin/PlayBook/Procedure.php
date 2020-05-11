<?php

namespace App\Model\Plugin\PlayBook;

use App\Model\Master\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Procedure extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_procedures';

    protected $with = ['procedures'];

    protected $fillable = [
        'procedure_id', 'code', 'content',
        'name', 'purpose', 'note', 'status', 'procedure_pending_id',
        'approval_request_by', 'approval_request_at',
        'approved_at', 'approval_request_to', 'approval_action',
        'declined_at'
    ];

    protected $dates = [
        'approved_at', 'approval_request_at'
    ];

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    public function histories()
    {
        return $this->hasMany(ProcedureHistory::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approval_request_to');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopeNotApprovedYet($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeApprovalRequested($query)
    {
        return $query->whereNull('approved_at')
            ->whereNotNull('approval_request_at');
    }

    public function scopeApprovalNotSent($query)
    {
        return $query->whereNull('approved_at')
            ->whereNull('approval_request_at');
    }

    public function scopeParent($query)
    {
        return $query->whereNull('procedure_id');
    }

    public function scopeFilter($query, Request $request)
    {
        return $query;
    }

    public function duplicateToHistory()
    {
        $me = $this->toArray();
        unset($me->procedure_id);

        $this->histories()->save(new ProcedureHistory(
            $me
        ));
    }
}
