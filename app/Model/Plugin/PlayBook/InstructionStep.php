<?php

namespace App\Model\Plugin\PlayBook;

use Illuminate\Database\Eloquent\Model;

class InstructionStep extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_instruction_steps';

    protected $fillable = [
        'instruction_id', 'name', 'status',
        'instruction_step_pending_id', 'approval_request_by',
        'approval_request_at', 'approved_at', 'declined_at',
        'approval_request_to', 'approval_action', 'approval_note',
    ];

    public function instruction()
    {
        return $this->belongsTo(Instruction::class);
    }

    public function contents()
    {
        return $this->hasMany(InstructionStepContent::class, 'step_id');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopeNotApprovedYet($query)
    {
        return $query->whereNull('approved_at')
            ->whereNull('declined_at');
    }

    public function scopeApprovalRequested($query)
    {
        return $query->whereNull('approved_at')
            ->whereNotNull('approval_request_at')
            ->whereNotNull('approval_request_to');
    }

    public function scopeApprovalNotSent($query)
    {
        return $query->whereNull('approved_at')
            ->whereNull('approval_request_at');
    }
}
