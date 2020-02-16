<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditFvaPaid extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_fva_paid';
}
