<?php

namespace App\Model\Finance\Cash;

use Illuminate\Database\Eloquent\Model;

class CashDetail extends Model
{
    protected $connection = 'tenant';

    protected $table = 'payment_cash_details';

    public $timestamps = false;
}
