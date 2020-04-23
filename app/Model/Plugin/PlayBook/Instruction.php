<?php

namespace App\Model\Plugin\PlayBook;

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

    public function scopeFilter($query, Request $request)
    {
        if ($request->has('procedure_id')) {
            $query->whereProcedureId($request->procedure_id);
        }
    }
}
