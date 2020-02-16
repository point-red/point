<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditInvoicePaid extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_invoice_paid';
}
