<?php

namespace App\Model\Plugin\PlayBook;

use Illuminate\Database\Eloquent\Model;

class GlossaryHistory extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_glossary_histories';

    protected $fillable = [
        'code', 'glossary_id', 'name', 'abbreviation', 'note',
    ];
}
