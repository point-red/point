<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditFvaCreated extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_fva_created';
}
