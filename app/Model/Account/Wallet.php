<?php

namespace App\Model\Account;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $connection = 'mysql';

    public static $alias = 'wallet';

    protected $table = 'wallets';

    protected $casts = [
        'amount' => 'double',
    ];
}
