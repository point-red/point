<?php

namespace App\Model\Finance\Payment;

class PaymentCashIn extends Payment
{
    protected $table = 'payments';

    protected $defaultNumberPrefix = 'CASH-IN';

    protected $disbursed = false;

}
