<?php

namespace App\Model\Plugin\PlayBook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Procedure extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_procedures';

    protected $with = ['procedures'];

    protected $fillable = [
        'intruction_number', 'procedure_id', 'code',
        'name', 'purpose', 'note', 'status'
    ];

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    public function scopeParent($query)
    {
        return $query->whereNull('procedure_id');
    }

    public function scopeFilter($query, Request $request)
    {
        return $query;
    }
}
