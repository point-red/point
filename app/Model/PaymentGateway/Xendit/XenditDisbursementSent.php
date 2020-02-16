<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditDisbursementSent extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_disbursement_sent';
}
