<?php

namespace App\Model\Plugin\PlayBook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProcedureHistory extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_procedure_histories';

    protected $fillable = [
        'procedure_id', 'code', 'content',
        'name', 'purpose', 'note', 'status',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function scopeFilter($query, Request $request)
    {
        return $query;
    }
}
