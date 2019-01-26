<?php

namespace App\Model\Finance\Cash;

use Illuminate\Database\Eloquent\Model;

class Cash extends Model
{
    protected $connection = 'tenant';

    protected $table = 'payment_cashes';

    public $timestamps = false;
}
