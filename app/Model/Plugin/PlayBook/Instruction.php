<?php

namespace App\Model\Plugin\PlayBook;

use App\Model\Master\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Instruction extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_instructions';

    protected $fillable = [
        'procedure_id', 'number', 'name', 'status'
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function steps()
    {
        return $this->hasMany(InstructionStep::class);
    }

    public function history()
    {
        return $this->hasOne(InstructionHistory::class);
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

    public function scopeFilter($query, Request $request)
    {
        if ($request->has('procedure_id')) {
            $query->whereProcedureId($request->procedure_id);
        }
    }
}