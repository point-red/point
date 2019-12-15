<?php

namespace App\Model\Reward;

use Illuminate\Database\Eloquent\Model;

class TokenGenerator extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'source',
        'amount',
        'is_active',
    ];
}
