<?php

namespace App\Model\Reward;

use Illuminate\Database\Eloquent\Model;

class TokenGenerator extends Model
{
    protected $connection = 'mysql';

    public static $alias = 'token_generator';

    protected $fillable = [
        'source',
        'amount',
        'is_active',
    ];
}
