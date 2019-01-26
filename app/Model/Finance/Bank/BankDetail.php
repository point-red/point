<?php

namespace App\Model\Finance\Bank;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $connection = 'tenant';

    protected $table = 'payment_bank_details';

    public $timestamps = false;
}
