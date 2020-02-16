<?php

namespace App\Model\PaymentGateway\Xendit;

use Illuminate\Database\Eloquent\Model;

class XenditRetailOutletPaid extends Model
{
    protected $connection = 'mysql';

    protected $table = 'xendit_retail_outlet_paid';
}
