<?php

namespace App\Model\Plugin\PlayBook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class InstructionStep extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_instruction_steps';

    protected $fillable = [
        'instruction_id', 'name', 'status'
    ];

    public function instruction()
    {
        return $this->belongsTo(Instruction::class);
    }

    public function contents()
    {
        return $this->hasMany(InstructionStepContent::class, 'step_id');
    }
}
