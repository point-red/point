<?php

namespace App\Model\Finance\Payment;

class PaymentBankIn extends Payment
{
    protected $table = 'payments';

    protected $defaultNumberPrefix = 'BANK-IN';

    protected $disbursed = false;
}
