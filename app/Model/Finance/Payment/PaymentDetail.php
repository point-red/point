<?php

namespace App\Model\Finance\Payment;

use App\Model\TransactionModel;

class PaymentDetail extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;
}
