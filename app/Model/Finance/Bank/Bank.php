<?php

namespace App\Model\Finance\Bank;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $connection = 'tenant';

    protected $table = 'payment_banks';

    public $timestamps = false;
}
