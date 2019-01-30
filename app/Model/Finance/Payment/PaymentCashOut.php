<?php

namespace App\Model\Finance\Payment;

class PaymentCashOut extends Payment
{

    protected $table = 'payments';

    protected $defaultNumberPrefix = 'CASH-OUT';

    protected $disbursed = true;

}
