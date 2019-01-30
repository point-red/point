<?php

namespace App\Model\Finance\Payment;

class PaymentBankOut extends Payment
{
    protected $table = 'payments';

    protected $defaultNumberPrefix = 'BANK-OUT';

    protected $disbursed = true;

}
