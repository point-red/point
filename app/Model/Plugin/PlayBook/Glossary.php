<?php

namespace App\Model\Plugin\PlayBook;

use Illuminate\Database\Eloquent\Model;

class Glossary extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_glossaries';

    protected $fillable = [
        'code', 'name', 'abbreviation', 'note'
    ];
}
