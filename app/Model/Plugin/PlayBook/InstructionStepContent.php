<?php

namespace App\Model\Plugin\PlayBook;

use Illuminate\Database\Eloquent\Model;

class InstructionStepContent extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_instruction_step_contents';

    protected $fillable = [
        'step_id', 'glossary_id', 'content',
    ];

    public function step()
    {
        return $this->belongsTo(InstructionStep::class);
    }

    public function glossary()
    {
        return $this->belongsTo(Glossary::class);
    }
}
