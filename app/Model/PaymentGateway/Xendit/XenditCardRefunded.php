<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditCardRefunded extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_card_refunded';
}
