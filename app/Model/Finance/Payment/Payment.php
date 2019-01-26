<?php

namespace App\Model\Finance\Payment;

use App\Model\TransactionModel;

class Payment extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;
}
