<?php

namespace App\Model\Plugin\PlayBook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Glossary extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_glossaries';

    protected $fillable = [
        'code', 'name', 'abbreviation', 'note',
    ];

    public function histories()
    {
        return $this->hasMany(GlossaryHistory::class);
    }

    public function scopeFilter($query, Request $request)
    {
        return $query
            ->where('code', 'like', "%{$request->search}%")
            ->orWhere('name', 'like', "%{$request->search}%");
    }

    public function duplicateToHistory()
    {
        $this->histories()->save(new GlossaryHistory($this->toArray()));
    }
}
